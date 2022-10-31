<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use TheMembers\MetricCourses;
use TheMembers\MetricUsers;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class MetricCoursesExport implements FromCollection, WithHeadings
{
    use Exportable;

    protected $array;
    protected $course;
    protected $tenant_id;

    public function __construct($id, $tenant_id)
    {
        $this->course = MetricCourses::where('course_id', $id)->first();
        $this->tenant_id = $tenant_id;
    }

    public function collection()
    {

        return MetricUsers::select('name_user', 'time_consumed', 'finished', 'percent_watched')->where('course_id', $this->course->course_id)->get();
    }

    public function headings(): array
    {

        $users = MetricUsers::where('course_id', $this->course->course_id)->where('tenant_id', $this->tenant_id)->first();
        return [
            ["Curso:", "Tempo Total:", "Tempo Consumido:", "Usuarios que Finalizaram:", "Engajamento:"],
            [$this->course->name_course, $this->course->time_total, $this->course->time_consumed, $this->course->users_finished, $this->course->users_finished_percented],
            ["", "", "", "", "", "", "", "", "", "", "", ""],
            ["Usuario:", "Tempo Consumido:", "Concluido:", "Porcentagem de Conclusão:"],
        ];
    }
}
