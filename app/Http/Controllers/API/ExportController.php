<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use phpDocumentor\Reflection\DocBlock\Tags\Author;
use TheMembers\Support\Message;
use TheMembers\Tenant\AppPlan;
use TheMembers\Tenant\TenantDomain;
use TheMembers\Tenant\TenantUsers;
use OpenApi\Annotations as OA;
use App\Models\Courses;
use App\Models\MetricCourses;
use TheMembers\ClassModuleSubscripton;
use TheMembers\Subscription\UserSubscription;
use TheMembers\ClassesHistories;
use TheMembers\Exports\MetricCoursesExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends BaseController
{
    public function exportCourse($id, $tenant_id) //$id de Curso
    {
        return Excel::download(new MetricCoursesExport($id, $tenant_id), 'users.xlsx');
    }
}
