<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\Machine;
use App\Models\MaintenanceType; 
use App\Models\Anomaly;
use App\Http\Requests\DailyReport\StoreDailyReportRequest;
use App\Http\Requests\DailyReport\UpdateDailyReportRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class DailyReportController extends Controller
{
    // Variables de renderizado de vistas, para evitar repetir código
    protected $index = 'DailyReport/Index';
    protected $form  = 'DailyReport/Form';

    // Constructor para aplicar políticas de autorización automáticamente
    public function __construct()
    {
        // Autorizar automáticamente métodos del Resource Controller usando políticas
        $this->authorizeResource(DailyReport::class, 'daily_report');
    }

    // ==============================
    // LISTADO
    // ==============================
    public function index()
    {
        return Inertia::render($this->index);
    }

    // ==============================
    // DATATABLE
    // ==============================
    public function table(Request $request)
    {
        $this->authorize('viewAny', DailyReport::class);

        $daily_reports = DailyReport::query()->with(['user', 'project', 'machine']);

        // Solo ver los suyos si no es administrador
        if (!auth()->user()->hasAnyRole(['Administrador','Super-Administrador'])) {
            $daily_reports->where('user_id', auth()->id());
        }

        if ($request->boolean('show_deleted')) {
            $daily_reports->withTrashed();
        }

        return DataTables::of($daily_reports)
            ->addColumn('deleted', fn ($daily_report) => $daily_report->trashed())
            ->make(true);
    }

    // ==============================
    // FORM CREAR
    // ==============================
    public function create()
    {
        $this->authorize('create', DailyReport::class);

        // Obtener último reporte del usuario para prellenar el formulario
        $last_daily_report = DailyReport::where('user_id', auth()->id())->latest()->first();

        // Si hay reporte abierto, redirigir a editar
        if ($last_daily_report && !$last_daily_report->finished_at) {
            return redirect()
                ->route('daily-reports.edit', $last_daily_report)
                ->with('warning', 'Tienes un reporte sin finalizar. Continúa con ese.');
        }

        return Inertia::render($this->form, [
            'lastReport'       => $last_daily_report,
            'projects'         => Project::select('id','name')->get(),
            'machines'         => Machine::select('id','plate','internal_id')->get(),
            'maintenanceTypes' => MaintenanceType::all(),
        ]);
    }

    // ==============================
    // FORM EDITAR
    // ==============================
    public function edit(DailyReport $daily_report)
    {
        // Carga relaciones necesarias para el formulario
        $daily_report->load([
            'project',
            'machine',
            'user',
            'maintenances',
            'maintenances.maintenanceType',
            'anomalies',
            'anomalies.media'
        ]);

        // Transformar media para Vue (agregar URLs) y adjuntarlo a cada anomalía
        $daily_report->anomalies->each(function ($anomaly) {
            $anomaly->media = $anomaly->getMedia('anomalies')->map(function ($m) {
                return [
                    'id' => $m->id,
                    'original_url' => $m->getUrl(),
                ];
            });
        });
        // Renderizar formulario con datos del reporte
        return Inertia::render($this->form, [
            'dailyReport'      => $daily_report,
            'projects'         => Project::select('id','name')->get(),
            'machines'         => Machine::select('id','plate','internal_id')->get(),
            'maintenanceTypes' => MaintenanceType::all(),
        ]);
    }

    // ==============================
    // STORE
    // ==============================
    public function store(StoreDailyReportRequest $request)
    {
        $this->authorize('create', DailyReport::class);

        $open_daily_report = DailyReport::where('user_id', auth()->id())
            ->whereNull('finished_at')
            ->latest()
            ->first();

        if ($open_daily_report) {
            return redirect()
                ->route('daily-reports.edit', $open_daily_report)
                ->with('warning', 'Tienes un reporte sin finalizar. Continúa con ese.');
        }

        DB::beginTransaction();

        try {

            $data         = $request->validated();
            $maintenances = $data['maintenances'] ?? [];
            $anomalies    = $data['anomalies'] ?? [];

            unset($data['maintenances'], $data['anomalies']);

            //crear reporte 
            $report = DailyReport::create([
                ...$data,
                'user_id' => auth()->id(),
            ]);

            //crear mantenciones relacionadas (si las hay)
            $filteredMaintenances = collect($maintenances)
                ->filter(fn ($m) =>
                    !empty($m['quantity']) || !empty($m['observation'])
                )
                ->values()
                ->toArray();

            if (!empty($filteredMaintenances)) {
                $report->maintenances()->createMany($filteredMaintenances);
            }

            // anomalías
            foreach ($anomalies as $index => $anom) {

                $hasPhotos = $request->hasFile("anomalies.$index.photos");
                $hasContent = !empty($anom['description']) || !empty($anom['severity']);

                // Si no tiene nada relevante → no crear
                if (!$hasContent && !$hasPhotos) {
                    continue;
                }

                $anomaly = $report->anomalies()->create([
                    'description' => $anom['description'] ?? '',
                    'severity'    => $anom['severity'] ?? null,
                ]);

                if ($hasPhotos) {

                    foreach ($request->file("anomalies.$index.photos") as $file) {

                        $anomaly
                            ->addMedia($file)
                            ->toMediaCollection('anomalies');
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('daily-reports.index')
                ->with('success', 'Reporte creado correctamente');

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()->with(
                'error',
                'Error al crear el reporte: ' . $e->getMessage()
            );
        }
    }

    // ==============================
    // UPDATE
    // ==============================
    public function update(UpdateDailyReportRequest $request, DailyReport $daily_report)
{
    DB::beginTransaction();

    try {
        $data = $request->validated();

        $maintenances = $data['maintenances'] ?? [];
        $anomalies    = $data['anomalies'] ?? [];

        // Limpiar datos para el update del modelo principal
        unset($data['maintenances'], $data['anomalies']);

        if ($request->boolean('is_finished')) {
            $data['finished_at'] = now();
        }

        // Actualizar reporte base
        $daily_report->update($data);

        // Sincronizar Mantenciones (Borrar y re-crear es lo más simple aquí)
        $daily_report->maintenances()->delete();
        if (!empty($maintenances)) {
            $filteredMaintenances = collect($maintenances)
                ->filter(fn ($m) => !empty($m['quantity']) || !empty($m['observation']))
                ->toArray();
            
            $daily_report->maintenances()->createMany($filteredMaintenances);
        }

        // Sincronizar Anomalías
        // Obtenemos los IDs que vienen del formulario (los que el usuario conservó)
        $incomingIds = collect($anomalies)->pluck('id')->filter()->toArray();

        // Eliminamos de la BD las que NO están en esa lista
        $daily_report->anomalies()->whereNotIn('id', $incomingIds)->delete();

        // Actualizar existentes o Crear nuevas
        foreach ($anomalies as $index => $anom) {
            
            $hasPhotos = $request->hasFile("anomalies.$index.photos");
            $hasContent = !empty($anom['description']) || !empty($anom['severity']);

            // Si no tiene nada y no tiene ID, ignoramos
            if (!$hasContent && !$hasPhotos && !isset($anom['id'])) {
                continue;
            }

            // Usamos updateOrCreate: si hay ID lo actualiza, si no, lo crea vinculado al reporte
            $anomaly = $daily_report->anomalies()->updateOrCreate(
                ['id' => $anom['id'] ?? null],
                [
                    'description' => $anom['description'] ?? '',
                    'severity'    => $anom['severity'] ?? null,
                ]
            );

            // Procesar fotos nuevas
            if ($hasPhotos) {
                foreach ($request->file("anomalies.$index.photos") as $file) {
                    $anomaly->addMedia($file)->toMediaCollection('anomalies');
                }
            }
        }

        DB::commit();

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Reporte actualizado correctamente');

    } catch (\Throwable $e) {
        DB::rollBack();

        return back()->with(
            'error',
            'Error al actualizar el reporte: ' . $e->getMessage()
        );
    }
}
    // ==============================
    // DELETE
    // ==============================
    public function destroy(DailyReport $daily_report)
    {
        $this->authorize('delete', $daily_report);
        $daily_report->delete();
        return back()->with('success', 'Reporte eliminado correctamente');
    }

    // ==============================
    // SHOW (PDF)
    // ==============================
    public function show(DailyReport $daily_report)
    {
        $this->authorize('view', $daily_report);

        $daily_report->load([
            'user',
            'project',
            'machine',
            'maintenances',
            'maintenances.maintenanceType',
            'anomalies',
            
        ]);

        // Convertir imágenes de anomalías a base64 para DomPDF
        foreach ($daily_report->anomalies as $anomaly) {

            $anomaly->media_base64 = $anomaly
                ->getMedia('anomalies')
                ->map(function ($media) {

                    $path = $media->getPath();

                    if (!file_exists($path)) {
                        return null;
                    }

                    $mime = mime_content_type($path);
                    $data = base64_encode(file_get_contents($path));

                    return [
                        'id' => $media->id,
                        'base64' => "data:$mime;base64,$data",
                    ];
                })
                ->filter() // elimina null si algún archivo no existe
                ->values();
        }
        // Renderizar PDF con la vista y los datos del reporte
        $pdf = Pdf::loadView('report.daily_report', [
            'title'  => 'Daily Report ' . $daily_report->id,
            'report' => $daily_report
        ])->setOptions([
            'isRemoteEnabled' => true,
        ]);

        return $pdf->stream('daily_report_' . $daily_report->id . '.pdf');
    }
}