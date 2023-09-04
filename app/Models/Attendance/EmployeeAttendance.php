<?php

namespace App\Models\Attendance;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
  use HasFactory;
  protected $guarded = [];


  /*Add Records*/
  public function store(array $req)
  {
    EmployeeAttendance::create($req);
  }
  /*Read Records by name*/

  public function readEmployeeAttendanceGroup($ob)
  {
    $schoolId = authUser()->school_id;
    return EmployeeAttendance::where('emp_id', $ob['empId'])
      ->where('attendance_date', $ob['attendanceDate'])
      // ->where('status', 1)
      // ->where('school_id', $schoolId)
      ->get();
  }

  /*Read Records by ID*/
  public function getGroupById($id)
  {
    $schoolId = authUser()->school_id;
    return DB::table('employee_attendances as a')
      ->select(
        DB::raw("a.description,a.academic_year,
        CONCAT(b.first_name,' ',b.middle_name,' ',b.last_name) as full_name,b.emp_no,
        CASE WHEN a.attendance_status = '0' THEN 'Absent'  
        WHEN a.attendance_status = '1' THEN 'Present'
        WHEN a.attendance_status = '2' THEN 'On Leave'
        END as attendance_status,
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.attendance_date::date,'dd-mm-yyyy') as attendance_date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('employees as b', 'b.id', '=', 'a.emp_id')
      ->where('a.id', $id)
      // ->where('a.school_id', $schoolId)
      ->first();
  }

  /*Read all Records by*/
  public function retrieveAll()
  {
    $schoolId = authUser()->school_id;
    return DB::table('employee_attendances as a')
      ->select(
        DB::raw("a.description,a.academic_year,
        CONCAT(b.first_name,' ',b.middle_name,' ',b.last_name) as full_name,b.emp_no,
        CASE WHEN a.attendance_status = '0' THEN 'Absent'  
        WHEN a.attendance_status = '1' THEN 'Present'
        WHEN a.attendance_status = '2' THEN 'On Leave'
        END as attendance_status,
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.attendance_date::date,'dd-mm-yyyy') as attendance_date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('employees as b', 'b.id', '=', 'a.emp_id')
      ->orderBy('a.id')
      // ->where('a.school_id', $schoolId)
      // ->where('status', 1)
      ->get();
  }

  //Get Records by name
  public function getEmployeeAttendance($req)
  {
    $academicYear = authUser()->academic_year;
    $attendanceData =  DB::table('students as a')
      ->select(
        DB::raw("a.description,a.academic_year,
      CONCAT(b.first_name,' ',b.middle_name,' ',b.last_name) as full_name,b.emp_no,
      CASE WHEN a.attendance_status = '0' THEN 'Absent'  
      WHEN a.attendance_status = '1' THEN 'Present'
      WHEN a.attendance_status = '2' THEN 'On Leave'
      END as attendance_status,
      CASE WHEN a.status = '0' THEN 'Deactivated'  
      WHEN a.status = '1' THEN 'Active'
      END as status,
      TO_CHAR(a.attendance_date::date,'dd-mm-yyyy') as attendance_date,
      TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
      ")
      )
      ->join('employees as b', 'b.id', '=', 'a.emp_id')
      ->where("a.academic_year", $academicYear)
      ->where("a.id", "Ilike", DB::raw("'%" . $req->search . "%'"))
      ->orderBy("a.attendance_date")
      ->get();
    return $attendanceData;

    // ->where("a.admission_no", "Ilike", DB::raw("'%" . $req->search . "%'"))      
    // ->orWhere(DB::raw('upper(b.class_name)'), 'LIKE', '%' . strtoupper($req->search) . '%')
    // ->orWhere(DB::raw('upper(c.section_name)'), 'LIKE', '%' . strtoupper($req->search) . '%')
    // // ->orWhere(DB::raw('upper(a.admission_no)'), 'LIKE', '%' . strtoupper($req->search) . '%')
    // // ->orWhere(DB::raw('upper(a.roll)'), 'LIKE', '%' . strtoupper($req->search) . '%')
    // ->orWhere(DB::raw('upper(a.gender_name)'), 'LIKE', '%' . strtoupper($req->search) . '%');
    // ->orWhere(DB::raw('upper(full_name)'), 'LIKE', '%' . strtoupper($req->search) . '%');
  }



  /*Read all Active Records*/
  public function active()
  {
    $schoolId = authUser()->school_id;
    return DB::table('employee_attendances as a')
      ->select(
        DB::raw("a.description,a.academic_year,
      CONCAT(b.first_name,' ',b.middle_name,' ',b.last_name) as full_name,b.emp_no,
      CASE WHEN a.attendance_status = '0' THEN 'Absent'  
      WHEN a.attendance_status = '1' THEN 'Present'
      WHEN a.attendance_status = '2' THEN 'On Leave'
      END as attendance_status,
      CASE WHEN a.status = '0' THEN 'Deactivated'  
      WHEN a.status = '1' THEN 'Active'
      END as status,
      TO_CHAR(a.attendance_date::date,'dd-mm-yyyy') as attendance_date,
      TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
      ")
      )
      ->join('employees as b', 'b.id', '=', 'a.emp_id')
      ->where('a.status', 1)
      // ->where('a.school_id', $schoolId)
      ->orderBy('a.id')
      ->get();
  }
}
