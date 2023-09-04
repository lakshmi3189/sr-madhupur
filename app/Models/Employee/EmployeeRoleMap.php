<?php

namespace App\Models\Employee;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRoleMap extends Model
{
    use HasFactory;
    protected $guarded = [];

    /*Add Records*/
    public function store(array $req)
    {
        EmployeeRoleMap::create($req);
    }

    /*Read Records by name*/
    public function readEmployeeRoleMapGroup($req)
    {
        return EmployeeRoleMap::where('emp_id', $req->empId)
            ->where('role_id', $req->roleId)
            ->where('status', 1)
            ->get();
    }

    /*Read Records by ID*/
    public function getGroupById($id)
    {
        return DB::table('employee_role_maps as a')
            ->select(
                DB::raw("a.id,a.role_id,a.emp_id,
                b.role_name,                
                concat(c.first_name,' ',c.middle_name,' ',c.last_name) as full_name, c.emp_no,
                CASE WHEN a.status = '0' THEN 'Deactivated'  
                WHEN a.status = '1' THEN 'Active'
                END as status,
                TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
                TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
                ")
            )
            ->join('roles as b', 'b.id', '=', 'a.role_id')
            ->join('employees as c', 'c.id', '=', 'a.emp_id')
            ->where('a.id', $id)
            ->where('a.status', 1)
            ->first();
    }

    /*Read all Records by*/
    public function retrieve()
    {
        return DB::table('employee_role_maps as a')
            ->select(
                DB::raw("a.id,a.role_id,a.emp_id,
            b.role_name,                
            concat(c.first_name,' ',c.middle_name,' ',c.last_name) as full_name, c.emp_no,
            CASE WHEN a.status = '0' THEN 'Deactivated'  
            WHEN a.status = '1' THEN 'Active'
            END as status,
            TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
            TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
            ")
            )
            ->join('roles as b', 'b.id', '=', 'a.role_id')
            ->join('employees as c', 'c.id', '=', 'a.emp_id')
            ->where('a.status', 1)
            ->orderByDesc('a.id');
        // ->get();
    }

    //Get Records by name
    public function searchByName($req)
    {
        return DB::table('employee_role_maps as a')
            ->select(
                DB::raw("a.id,a.role_id,a.emp_id,
            b.role_name,                
            concat(c.first_name,' ',c.middle_name,' ',c.last_name) as full_name, c.emp_no,
            CASE WHEN a.status = '0' THEN 'Deactivated'  
            WHEN a.status = '1' THEN 'Active'
            END as status,
            TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
            TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
            ")
            )
            ->join('roles as b', 'b.id', '=', 'a.role_id')
            ->join('employees as c', 'c.id', '=', 'a.emp_id')
            ->where(DB::raw('upper(b.role_name)'), 'LIKE', '%' . strtoupper($req->search) . '%')
            ->where(DB::raw('upper(c.emp_no)'), 'LIKE', '%' . strtoupper($req->search) . '%');
    }

    /*Read all Active Records*/
    public function active()
    {
        return DB::table('employee_role_maps as a')
            ->select(
                DB::raw("a.id,a.role_id,a.emp_id,
            b.role_name,                
            concat(c.first_name,' ',c.middle_name,' ',c.last_name) as full_name, c.emp_no,
            CASE WHEN a.status = '0' THEN 'Deactivated'  
            WHEN a.status = '1' THEN 'Active'
            END as status,
            TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
            TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
            ")
            )
            ->join('roles as b', 'b.id', '=', 'a.role_id')
            ->join('employees as c', 'c.id', '=', 'a.emp_id')
            ->where('a.status', 1)
            ->orderBy('a.id')
            ->get();
    }
}
