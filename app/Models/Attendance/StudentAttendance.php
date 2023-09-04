<?php

namespace App\Models\Attendance;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
  use HasFactory;
  protected $guarded = [];


  /*Add Records*/
  public function store(array $req)
  {
    StudentAttendance::create($req);
  }
  /*Read Records by name*/

  public function readStudentAttendanceGroup($ob)
  {
    $schoolId = authUser()->school_id;
    return StudentAttendance::where('class_id', $ob['classId'])
      // ->orWhere('student_id', $ob['sectionId'])
      ->where('student_id', $ob['studentId'])
      ->where('attendance_date', $ob['attendanceDate'])
      // ->where('status', 1)
      // ->where('school_id', $schoolId)
      ->get();
  }

  /*Read Records by ID*/
  public function getGroupById($id)
  {
    $schoolId = authUser()->school_id;
    return DB::table('student_attendances as a')
      ->select(
        DB::raw("b.class_name, d.section_name, CONCAT(c.first_name,' ',c.middle_name,' ',c.last_name) as full_name ,c.admission_no,c.roll_no, a.*,
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('class_masters as b', 'b.id', '=', 'a.class_id')
      ->join('students as c', 'c.id', '=', 'a.student_id')
      ->leftjoin('sections as d', 'd.id', '=', 'a.section_id')
      ->where('a.id', $id)
      // ->where('a.school_id', $schoolId)
      ->first();
  }

  /*Read all Records by*/
  public function retrieveAll()
  {
    $schoolId = authUser()->school_id;
    return DB::table('student_attendances as a')
      ->select(
        DB::raw("b.class_name, CONCAT(c.first_name,' ',c.middle_name,' ',c.last_name) as full_name ,c.admission_no,c.roll_no, a.*,
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('class_masters as b', 'b.id', '=', 'a.class_id')
      ->join('students as c', 'c.id', '=', 'a.student_id')
      // ->leftJoin('sections as d', 'd.id', '=', 'a.section_id')
      ->orderBy('a.id')
      // ->where('a.school_id', $schoolId)
      // ->where('status', 1)
      ->get();
  }

  //Get Records by name
  public function getStudentAttendance($req)
  {
    $academicYear = authUser()->academic_year;
    $attendanceData =  DB::table('students as a')
      ->select(
        DB::raw("b.class_name, c.section_name,
        CONCAT(a.first_name,'',a.middle_name,' ',a.last_name) as full_name, 
        a.admission_no,a.roll_no,a.id,a.class_name,a.section_name,a.gender_name,a.blood_group_name,
        d.attendance_status,d.description,d.attendance_date,d.academic_year,
        CASE WHEN d.attendance_status = '0' THEN 'Absent'  
        WHEN d.attendance_status = '1' THEN 'Present'
        WHEN d.attendance_status = '2' THEN 'On Leave'
        END as attendance_status,
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(d.attendance_date::date,'dd-mm-yyyy') as attendance_date,
        TO_CHAR(d.created_at,'HH12:MI:SS AM') as attendance_time
        ")
      )
      ->join('student_attendances as d', 'd.student_id', '=', 'a.id')
      ->join('class_masters as b', 'b.id', '=', 'd.class_id')
      ->leftjoin('sections as c', 'c.id', '=', 'd.section_id')
      ->where("d.academic_year", $academicYear)
      ->where("a.id", "Ilike", DB::raw("'%" . $req->search . "%'"))
      ->orderBy("d.attendance_date")
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
    return DB::table('student_attendances as a')
      ->select(
        DB::raw("b.class_name, d.section_name, CONCAT(c.first_name,' ',c.middle_name,' ',c.last_name) as full_name ,c.admission_no,c.roll_no, a.*,
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('class_masters as b', 'b.id', '=', 'a.class_id')
      ->join('students as c', 'c.id', '=', 'a.student_id')
      ->leftjoin('sections as d', 'd.id', '=', 'a.section_id')
      ->where('a.status', 1)
      // ->where('a.school_id', $schoolId)
      ->orderBy('a.id')
      ->get();
  }
}
