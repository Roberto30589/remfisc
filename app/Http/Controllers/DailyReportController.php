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
            'maintenances.maintenanceType'
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

        //buscamos si el usuario tiene un reporte abierto (sin finished_at)
        $open_daily_report = DailyReport::where('user_id', auth()->id())
            ->whereNull('finished_at')
            ->latest()
            ->first();
        
        //Si el usuario tiene un reporte abierto, no se le permite crear uno nuevo hasta que termine el anterior. En su lugar, se le redirige al reporte abierto para que lo complete.
        if ($open_daily_report) {
            return redirect()
                ->route('daily-reports.edit', $open_daily_report)
                ->with('warning', 'Tienes un reporte sin finalizar. Continúa con ese.');
        }

        //Si no tiene reportes abiertos, se le permite crear uno nuevo
        DB::transaction(function () use ($request) {

            $data = $request->validated();

            $maintenances = $data['maintenances'] ?? [];
            unset($data['maintenances']);

            $report = DailyReport::create([
                ...$data,
                'user_id' => auth()->id(),
            ]);

            // Filtrar mantenciones vacías antes de guardar
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
        });

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Reporte creado correctamente');
    }

    // UPDATE
    public function update(UpdateDailyReportRequest $request, DailyReport $daily_report)
    {
        $this->authorize('update', $daily_report);

        DB::transaction(function () use ($request, $daily_report) {

            $data = $request->validated();

            $maintenances = $data['maintenances'] ?? [];
            unset($data['maintenances']);

            if ($request->boolean('is_finished')) {
                $data['finished_at'] = now();
            }

            $daily_report->update($data);

            // borrar mantenciones anteriores (soft delete)
            $daily_report->maintenances()->delete();

            // Filtrar mantenciones vacías
            $filtered = collect($maintenances)
                ->filter(fn ($m) =>
                    !empty($m['quantity']) ||
                    !empty($m['observation'])
                )
                ->values()
                ->toArray();

            if (!empty($filtered)) {
                $daily_report->maintenances()->createMany($filtered);
            }
        });

        $message = $request->boolean('is_finished')
            ? 'Reporte terminado correctamente'
            : 'Reporte actualizado correctamente';

        return redirect()
            ->route('daily-reports.index')
            ->with('success', $message);
    }

    // DELETE
    public function destroy(DailyReport $daily_report)
    {
        $this->authorize('delete', $daily_report);
        $daily_report->delete();
        return back()->with('success', 'Reporte eliminado correctamente');
    }

    // PDF (método personalizado → requiere authorize manual)
    public function show(DailyReport $daily_report)
    {
        $this->authorize('view', $daily_report);

        $daily_report->load([
            'user',
            'project',
            'machine',
            'maintenances',
            'maintenances.maintenanceType'
        ]);

        $pdf = Pdf::loadView('report.daily_report', [
            'title'  => 'Daily Report ' . $daily_report->id,
            'report' => $daily_report
        ]);

        return $pdf->stream('daily_report_' . $daily_report->id . '.pdf');
    }
}