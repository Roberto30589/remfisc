<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\Machine;
use App\Http\Requests\DailyReport\StoreDailyReportRequest;
use App\Http\Requests\DailyReport\UpdateDailyReportRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class DailyReportController extends Controller
{
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
        return Inertia::render('DailyReport/Index');
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

        $last_daily_report = DailyReport::where('user_id', auth()->id())
            ->whereNull('finished_at')
            ->latest()
            ->first();

        return Inertia::render('DailyReport/Form', [
            'dailyReport' => $last_daily_report,
            'projects'    => Project::select('id','name')->get(),
            'machines'    => Machine::select('id','plate','internal_id')->get(),
            'infoMessage' => $last_daily_report 
                ? 'Tienes un reporte sin finalizar. Continuando desde donde quedaste.'
                : null,
        ]);
    }

    // FORM EDITAR
    public function edit(DailyReport $daily_report)
    {
        $daily_report->load(['project','machine','user']);
        return Inertia::render('DailyReport/Form', [
            'dailyReport' => $daily_report,
            'projects'    => Project::select('id','name')->get(),
            'machines'    => Machine::select('id','plate','internal_id')->get(),
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
                ->with('info', 'Tienes un reporte sin finalizar. Continúa con ese.');
        }

        //Si no tiene reportes abiertos, se le permite crear uno nuevo
        DailyReport::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Reporte creado correctamente');
    }

    // UPDATE
    public function update(UpdateDailyReportRequest $request, DailyReport $daily_report)
    {
        $this->authorize('update', $daily_report);
        $data = $request->validated();
        if ($request->boolean('is_finished')) {
            $data['finished_at'] = now();
        }
        $daily_report->update($data);
        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Reporte actualizado correctamente');
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
        $daily_report->load(['user','project','machine']);
        $pdf = Pdf::loadView('report.daily_report', [
            'title'  => 'Daily Report ' . $daily_report->id,
            'report' => $daily_report
        ]);
        return $pdf->stream('daily_report_' . $daily_report->id . '.pdf');
    }
}
