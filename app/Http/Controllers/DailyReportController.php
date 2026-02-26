<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\Machine;
use App\Models\MaintenanceType; 
use App\Http\Requests\DailyReport\StoreDailyReportRequest;
use App\Http\Requests\DailyReport\UpdateDailyReportRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class DailyReportController extends Controller
{
    //variables de renderizado de vistas, para evitar repetir código
    protected $index = 'DailyReport/Index';
    protected $form = 'DailyReport/Form';

    public function __construct()
    {
        //Utilizando los nombre de los métodos convencionales de un Resource Controller, podemos aplicar la autorización automáticamente con authorizeResource
        //Esto aplicará las políticas correspondientes a cada método (index → viewAny, show → view, create → create, edit/update → update, destroy → delete)
        //Creamos la política con php artisan make:policy DailyReportPolicy --model=DailyReport
        $this->authorizeResource(DailyReport::class, 'daily_report');
    }

    // LISTADO
    public function index()
    {
        return Inertia::render($this->index);
    }

    // DATATABLE
    public function table(Request $request)
    {
        $this->authorize('viewAny', DailyReport::class);
        $daily_reports = DailyReport::query()
            ->with(['user', 'project', 'machine']);

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

    // FORM CREAR
    public function create()
    {
        $this->authorize('create', DailyReport::class);
        
        //Buscamos el último reporte del usuario para prellenar el formulario con esos datos (excepto fecha, km/hm y descripción de trabajo que se dejan en blanco para que el usuario los complete)
        $last_daily_report = DailyReport::where('user_id', auth()->id())->latest()->first();

        //Si el último reporte no tiene fecha de finalización (finished_at), se le redirige a ese reporte para que lo complete en lugar de crear uno nuevo
        if ($last_daily_report && !$last_daily_report->finished_at) {
            return redirect()
                ->route('daily-reports.edit', $last_daily_report)
                ->with('warning', 'Tienes un reporte sin finalizar. Continúa con ese.');
        }

        return Inertia::render($this->form, [
            'lastReport'  => $last_daily_report, // Para prellenar el formulario con los datos del último reporte abierto
            'projects'    => Project::select('id','name')->get(),
            'machines'    => Machine::select('id','plate','internal_id')->get(),

            // En lugar de enviar un array fijo de mantenciones, se envían las mantenciones definidas en la base de datos para que el formulario sea dinámico y se puedan agregar nuevas mantenciones sin necesidad de modificar el código.
            'maintenanceTypes' => MaintenanceType::all(),
        ]);
    }

    // FORM EDITAR
    public function edit(DailyReport $daily_report)
    {
        $daily_report->load([
            'project',
            'machine',
            'user',
            'maintenances',
            'maintenances.maintenanceType',
            'anomalies',
            'anomalies.pictures'
        ]);

        return Inertia::render($this->form, [
            'dailyReport' => $daily_report,
            'projects'    => Project::select('id','name')->get(),
            'machines'    => Machine::select('id','plate','internal_id')->get(),
            //
            'maintenanceTypes' => MaintenanceType::all(),
        ]);
    }

    // STORE
    public function store(StoreDailyReportRequest $request)
    {
        $this->authorize('create', DailyReport::class);
        // Verificar si el usuario tiene un reporte abierto (sin fecha de finalización)
        $open_daily_report = DailyReport::where('user_id', auth()->id())
            ->whereNull('finished_at')
            ->latest()
            ->first();
        // Si tiene un reporte abierto, redirigirlo a ese reporte en lugar de crear uno nuevo
        if ($open_daily_report) {
            return redirect()
                ->route('daily-reports.edit', $open_daily_report)
                ->with('warning', 'Tienes un reporte sin finalizar. Continúa con ese.');
        }
        // Si no tiene reportes abiertos, crear uno nuevo
        DB::transaction(function () use ($request) {

            $data = $request->validated();

            $maintenances = $data['maintenances'] ?? [];
            $anomalies    = $data['anomalies'] ?? [];

            unset($data['maintenances'], $data['anomalies']);

            //Crear reporte
            $report = DailyReport::create([
                ...$data,
                'user_id' => auth()->id(),
            ]);

            // Guardar mantenciones
            $filtered = collect($maintenances)
                ->filter(fn ($m) =>
                    !empty($m['quantity']) ||
                    !empty($m['observation'])
                )
                ->values()
                ->toArray();

            if (!empty($filtered)) {
                $report->maintenances()->createMany($filtered);
            }

            //Guardar anomalías + fotos
            foreach ($anomalies as $index => $anom) {

                // Si no tiene descripción ni fotos → saltar
                if (empty($anom['description']) && empty($anom['photos'])) {
                    continue;
                }

                $anomaly = $report->anomalies()->create([
                    'description' => $anom['description'] ?? '',
                    'severity'    => $anom['severity'] ?? null,
                ]);

                // Guardar fotos correctamente
                if ($request->hasFile("anomalies.$index.photos")) {

                    foreach ($request->file("anomalies.$index.photos") as $file) {

                        $path = $file->store('anomalies', 'public');

                        $anomaly->pictures()->create([
                            'path' => $path
                        ]);
                    }
                }
            }

        });

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Reporte creado correctamente');
    }

    // UPDATE
    public function update(UpdateDailyReportRequest $request, DailyReport $daily_report)
    {
        $this->authorize('update', $daily_report);

        $data = $request->validated();

        $maintenances = $data['maintenances'] ?? [];
        $anomalies    = $data['anomalies'] ?? [];

        unset($data['maintenances'], $data['anomalies']);

        if ($request->boolean('is_finished')) {
            $data['finished_at'] = now();
        }
        // Utilizamos transacciones para asegurarnos de que todas las operaciones relacionadas con la actualización del reporte se realicen correctamente. Si ocurre un error en cualquier parte del proceso, se hará un rollback y no se guardarán cambios parciales.
        DB::beginTransaction();

        try {
            //ACTUALIZAR REPORTE
            $daily_report->update($data);
            // mantenciones
            $daily_report->maintenances()->delete();
            // Filtrar mantenciones para eliminar aquellas que no tienen cantidad ni observación, evitando crear registros vacíos
            $filteredMaintenances = collect($maintenances)
                ->filter(fn ($m) =>
                    !empty($m['quantity']) ||
                    !empty($m['observation'])
                )
                ->values()
                ->toArray();

            if (!empty($filteredMaintenances)) {
                $daily_report->maintenances()->createMany($filteredMaintenances);
            }
            

            // anomalías
            // IDs que vienen desde el frontend
            $incomingIds = collect($anomalies)
                ->pluck('id')
                ->filter()
                ->toArray();

            // Eliminar anomalías removidas
            $daily_report->anomalies()
                ->whereNotIn('id', $incomingIds)
                ->get()
                ->each(function ($anomaly) {
                    foreach ($anomaly->pictures as $pic) {
                        if (\Storage::disk('public')->exists($pic->path)) {
                            \Storage::disk('public')->delete($pic->path);
                        }
                        $pic->delete();
                    }
                    $anomaly->delete();
                });

            // Crear o actualizar anomalías
            foreach ($anomalies as $index => $anom) {

                if (!empty($anom['id'])) {
                    $anomaly = $daily_report->anomalies()->find($anom['id']);

                    if ($anomaly) {
                        $anomaly->update([
                            'description' => $anom['description'] ?? '',
                            'severity'    => $anom['severity'] ?? null,
                        ]);
                    }
                } else {
                    $anomaly = $daily_report->anomalies()->create([
                        'description' => $anom['description'] ?? '',
                        'severity'    => $anom['severity'] ?? null,
                    ]);
                }

                if (!$anomaly) continue;

                // IDs de fotos que vienen desde el frontend para esta anomalía
                $existingIds = collect($anom['existing_photos'] ?? [])
                    ->map(function ($photo) {
                        if (is_array($photo) && isset($photo['id'])) {
                            return $photo['id'];
                        }
                        if (is_numeric($photo)) {
                            return $photo;
                        }
                        return null;
                    })
                    ->filter()
                    ->values()
                    ->toArray();

                // Eliminar fotos quitadas
                $anomaly->pictures()
                    ->whereNotIn('id', $existingIds)
                    ->get()
                    ->each(function ($pic) {
                        if (\Storage::disk('public')->exists($pic->path)) {
                            \Storage::disk('public')->delete($pic->path);
                        }
                        $pic->delete();
                    });

                // Guardar nuevas fotos
                if ($request->hasFile("anomalies.$index.photos")) {
                    foreach ($request->file("anomalies.$index.photos") as $file) {
                        $path = $file->store('anomalies', 'public');
                        $anomaly->pictures()->create(['path' => $path]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('daily-reports.index')
                ->with('success', 'Reporte actualizado correctamente');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Error al actualizar el reporte');
        }
    }

    // DELETE
    public function destroy(DailyReport $daily_report)
    {
        $this->authorize('delete', $daily_report);
        $daily_report->delete();
        return back()->with('success', 'Reporte eliminado correctamente');
    }

    // PDF VIEW
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
            'anomalies.pictures'
        ]);

        //Convertir imágenes a base64 AQUÍ (no en el blade)
        foreach ($daily_report->anomalies as $anomaly) {
            foreach ($anomaly->pictures as $pic) {

                $path = public_path('storage/' . $pic->path);

                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $pic->base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                } else {
                    $pic->base64 = null;
                }
            }
        }

        $pdf = Pdf::loadView('report.daily_report', [
            'title'  => 'Daily Report ' . $daily_report->id,
            'report' => $daily_report
        ])->setOptions([
            'isRemoteEnabled' => true,
        ]);

        return $pdf->stream('daily_report_' . $daily_report->id . '.pdf');
    }


}