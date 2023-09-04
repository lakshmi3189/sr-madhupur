<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Exception;
use DB;
use App\Models\Student\StudentSibling;
use App\Models\Admin\StudentTransport;
use App\Models\Admin\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/*
Created By : Lakshmi kumari 
Created On : 19-May-2023 
Code Status : Open 
*/

class Student extends Model
{
  use HasApiTokens, HasFactory, Notifiable;
  //use HasFactory;
  protected $guarded = [];

  /**---------------------------------------------------------------------------------------------
   * |Also used in BLL 
   */
  // //Read all students    
  // public function readStudentGroup($admissionNo)
  // {
  //   return Student::where('admission_no', $admissionNo)
  //     ->where('status', 1)
  //     ->where('school_id', authUser()->school_id)
  //     ->get();
  // }

  //read online student validation
  public function readOnlineStudentGroup($req)
  {
    return Student::where('admission_no', $req->admissionNo)
      ->where('status', 1)
      // ->where('school_id', $req->schoolId)
      ->get();
  }

  //Read all students    
  public function readStudentGroup($req)
  {

    return Student::where('admission_no', $req->admissionNo)
      ->where('status', 1)
      // ->where('school_id', authUser()->school_id)
      ->get();
  }

  // //Read all students financial year wise
  // public function getStudentsByFy($fy)
  // {
  //   return Student::where('academic_year', $fy)
  //     // ->where('academic_year', getFinancialYear(Carbon::now()->format('Y-m-d')))
  //     ->where('school_id', authUser()->school_id)
  //     ->where('status', 1)
  //     ->get();
  // }
  /*-------------------------------------------------------------------------------------------*/

  /*Add Records*/
  public function store(array $req)
  {
    Student::create($req);
  }

  /*Read Records by ID*/
  public function getclassMatesGroupById($req, $classId)
  {
    //TO_CHAR(a.admission_date::date,'dd-mm-yyyy') as admission_date,
    $std =  DB::table('students as a')
      ->select(
        DB::raw("admission_no,CONCAT(first_name,'',middle_name,' ',last_name) as full_name ,
       roll_no,id,class_name,section_name,gender_name
       ")
      )
      ->where('class_id', $classId)
      ->where('status', 1)
      ->orderBy('first_name')
      ->get();
    // $path = "getImageLink?path=";
    // $std->upload_image = trim($std->upload_image) ? ($path . $std->upload_image) : "";
    // $std->role_name = "Parents";
    // $std->role_id = "9";
    return $std;
  }

  /*Read Records by ID*/
  public function getParentGroupById($id)
  {
    //TO_CHAR(a.admission_date::date,'dd-mm-yyyy') as admission_date,
    $std =  DB::table('students as a')
      ->select(
        DB::raw("CONCAT(a.first_name,'',a.middle_name,' ',a.last_name) as full_name ,
         a.admission_no,a.roll_no,a.id,a.class_name,a.class_id,a.section_name,a.section_id,a.gender_name,a.blood_group_name,a.email,a.mobile, 
         a.category_name,a.upload_image as upload_image,a.admission_mid_session,a.admission_month,a.fathers_name,a.fathers_mob_no,a.fathers_email,
         a.mothers_name,a.mothers_mob_no,a.mothers_email,a.guardian_name,a.guardian_mob_no,a.guardian_email,a.guardian_relation_name,
         a.p_address1,a.p_address2,a.p_locality,a.p_landmark,a.p_country_name,a.p_state_name,a.p_district_name,a.p_pincode,
         a.c_address1,a.c_address2,a.c_locality,a.c_landmark,a.c_country_name,a.c_state_name,a.c_district_name,a.c_pincode,
         a.bank_name,a.account_no,a.ifsc_code,a.branch_name,a.academic_year,a.role_name,a.user_name, 
         CASE WHEN a.disability = '0' THEN 'No'  
         WHEN a.disability = '1' THEN 'Yes'
         END as disability,
         CASE WHEN a.is_transport = '0' THEN 'No'  
         WHEN a.is_transport = '1' THEN 'Yes'
         END as is_transport,              
         CASE WHEN a.status = '0' THEN 'Deactivated'  
         WHEN a.status = '1' THEN 'Active'
         END as status,         
         TO_CHAR(a.dob::date,'dd-mm-yyyy') as dob,
         TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
         TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
         ")
      )
      ->join('roles as b', 'b.id', '=', 'a.role_id')
      ->where('a.id', $id)
      // ->orWhere('b.id', 9)
      ->where('a.status', 1)
      ->first();
    $path = "getImageLink?path=";
    $std->upload_image = trim($std->upload_image) ? ($path . $std->upload_image) : "";
    $std->role_name = "Parents";
    $std->role_id = "9";
    return $std;
  }


  /*Read Records by ID*/
  public function getGroupById($id)
  {
    // TO_CHAR(a.admission_date::date,'dd-mm-yyyy') as admission_date,
    $std =  DB::table('students as a')
      ->select(
        DB::raw("CONCAT(a.first_name,'',a.middle_name,' ',a.last_name) as full_name ,
        a.admission_no,a.roll_no,a.id,a.class_name,a.class_id,a.section_name,a.section_id,a.gender_name,a.blood_group_name,a.email,a.mobile, 
        a.category_name,a.upload_image as upload_image,a.admission_mid_session,a.admission_month,a.fathers_name,a.fathers_mob_no,a.fathers_email,
        a.mothers_name,a.mothers_mob_no,a.mothers_email,a.guardian_name,a.guardian_mob_no,a.guardian_email,a.guardian_relation_name,
        a.p_address1,a.p_address2,a.p_locality,a.p_landmark,a.p_country_name,a.p_state_name,a.p_district_name,a.p_pincode,
        a.c_address1,a.c_address2,a.c_locality,a.c_landmark,a.c_country_name,a.c_state_name,a.c_district_name,a.c_pincode,
        a.bank_name,a.account_no,a.ifsc_code,a.branch_name,a.academic_year,a.role_name,a.role_id,a.user_name, 
        b.role_name,
        CASE WHEN a.disability = '0' THEN 'No'  
        WHEN a.disability = '1' THEN 'Yes'
        END as disability,
        CASE WHEN a.is_transport = '0' THEN 'No'  
        WHEN a.is_transport = '1' THEN 'Yes'
        END as is_transport,              
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,       
        TO_CHAR(a.dob::date,'dd-mm-yyyy') as dob,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('roles as b', 'b.id', '=', 'a.role_id')
      ->where('a.id', $id)
      ->where('a.status', 1)
      ->first();
    $path = "getImageLink?path=";
    $std->upload_image = trim($std->upload_image) ? ($path . $std->upload_image) : "";
    return $std;
  }


  /*Read all Records by*/
  public function retrieveAll()
  {
    $schoolId = authUser()->school_id;
    // $createdBy = authUser()->id;
    return Student::select(
      '*',
      DB::raw("
      CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
        WHEN status = '2' THEN 'Deactivated'
        WHEN status = '3' THEN 'Deactivated'
        END as status,
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time
	  ")
    );
    // ->where('school_id', $schoolId)
    // ->orWhere('created_by', $createdBy)
    // ->where('status', 1)
    // ->get();
  }

  public function getStudentGroupBySection($req)
  {
    // return $req->id; 
    $schoolId = authUser()->school_id;
    // $createdBy = authUser()->id;
    // c.section_name ,
    return DB::table('students as a')
      ->select(
        DB::raw("b.class_name,CONCAT(a.first_name,'',a.middle_name,' ',a.last_name) as full_name ,
        a.admission_no,a.roll_no,a.id,               
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('class_masters as b', 'b.id', '=', 'a.class_id')
      // ->leftjoin('section_group_maps as c', 'c.id', '=', 'a.section_id')
      ->where('a.class_id', $req->classId)
      // ->orWhere('a.section_id', $req->id)
      ->where('a.status', 1)
      // ->where('a.school_id', $schoolId)
      // ->where('a.created_by', $createdBy)
      ->get();
  }

  public function getStudentGroupBySection2($req)
  {
    $schoolId = authUser()->school_id;
    // $createdBy = authUser()->id;
    return DB::table('students as a')
      ->select(
        DB::raw("b.class_name,CONCAT(a.first_name,' ',a.middle_name,' ',a.last_name) as full_name ,
        a.admission_no,a.roll_no,c.section_name,a.id,        
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('class_masters as b', 'b.id', '=', 'a.class_id')
      ->leftjoin('section_group_maps as c', 'c.id', '=', 'a.section_id')
      ->where('a.class_id', $req->classId)
      ->orWhere('a.section_id', $req->id)
      ->where('a.status', 1)
      // ->where('a.school_id', $schoolId)
      // ->where('a.created_by', $createdBy)
      ->get();
  }

  public static function csv($data)
  {

    // $value = DB::table('users')->where('username', $data['username'])->get();
    // if ($value->count() == 0) {
    DB::table('students')->insert($data);
    // }
  }


  //Search student by using admission no
  public function searchAdmNo($req)
  {
    $schoolId = authUser()->school_id;
    // $createdBy = authUser()->id;
    $checkExist = Student::where([['admission_no', '=', $req->admissionNo], ['status', '=', '1']])
      // ->where('school_id', $schoolId)
      // ->where('created_by', $createdBy)
      ->count();
    $data = array();
    if ($checkExist > 0) {
      $data =  ['admission_no' => $req->admissionNo, 'message' => 'Admission No. already existing', 'value' => 'true'];
    }
    if ($checkExist == 0) {
      $data = ['admission_no' => $req->admissionNo, 'message' => 'Admission No. not found', 'value' => 'false'];
    }
    return $data;
  }

  public function getStudentIdDetails($req, $id)
  {
    $schoolId = authUser()->school_id;
    // $createdBy = authUser()->id;
    return DB::table('students as a')
      ->select(
        DB::raw("b.class_name, c.section_name,
        CONCAT(a.first_name,' ',a.middle_name,' ',a.last_name) as full_name ,a.admission_no,a.roll_no,
        TO_CHAR(a.dob::date, 'DD-MM-YYYY') as dob,a.blood_group_name,a.email, a.p_address1,a.mobile,a.academic_year,
          CASE WHEN a.status = '0' THEN 'Deactivated'  
          WHEN a.status = '1' THEN 'Active'
          END as status,
          TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
          TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
          ")
      )
      ->join('class_masters as b', 'b.id', '=', 'a.class_id')
      ->leftjoin('section_group_maps as c', 'c.id', '=', 'a.section_id')
      // ->join('sections as c', 'c.id', '=', 'a.section_id')
      ->where('a.class_id', $req->classId)
      // ->where('a.section_id', $req->sectionId)
      ->where('a.id', $id)
      ->where('a.status', 1)
      // ->where('a.school_id', $schoolId)
      // ->where('a.created_by', $createdBy)
      ->orderBy('a.id')
      ->get();
  }

  /*Read Records by ID*/
  public function readRoleExist($req)
  {
    $schoolId = authUser()->school_id;
    // $createdBy = authUser()->id;
    return Student::where('id', $req->id)
      ->where('role_id', $req->roleId)
      // ->where('school_id', $schoolId)
      // ->where('created_by', $createdBy)
      ->where('status', 1)
      ->first();
  }

  /** ----------------Reporting Method----------------------------------------------------------- */

  /*Read all Records by*/
  public function getAllStudent($req, $schoolId)
  {
    //$schoolId = authUser()->school_id;
    return DB::table('students as a')
      ->select(
        DB::raw("b.class_name, c.section_name,
          CONCAT(a.first_name,' ',a.middle_name,' ',a.last_name) as full_name ,a.admission_no,a.roll_no,
          TO_CHAR(a.dob::date, 'DD-MM-YYYY') as dob,a.blood_group_name,a.email, a.p_address1,a.mobile,a.academic_year,
          e.sub_total,e.payment_date,
          CASE WHEN e.is_paid = '0' THEN 'Not Paid'  
          WHEN e.is_paid = '1' THEN 'Paid'
          END as payment_status,
          CASE WHEN a.status = '0' THEN 'Deactivated'  
          WHEN a.status = '1' THEN 'Active'
          END as status,
          TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
          TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
          ")
      )
      ->join('class_masters as b', 'b.id', '=', 'a.class_id')
      ->leftjoin('section_group_maps as c', 'c.id', '=', 'a.section_id')
      // ->join('fee_collections as d', 'd.student_id', '=', 'a.id')
      ->join('payments as e', 'e.student_id', '=', 'a.id')
      ->where('a.status', 1)
      // ->where('a.school_id', $schoolId)
      ->where('a.academic_year', $req->financialYear)
      ->where('e.is_paid', $req->paymentStatus)
      ->orderBy('a.class_id')
      ->get();
  }




  /*Count all Active Records*/
  public function countActive()
  {
    return Student::where('status', 1)->count();
  }

  //Get Records by name
  public function searchByName($req)
  {
    return Student::select(
      '*',
      DB::raw("
      CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
        WHEN status = '2' THEN 'Deactivated'
        WHEN status = '3' THEN 'Deactivated'
        END as status,
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time
	  ")
    )
      ->where(DB::raw('upper(admission_no)'), 'LIKE', '%' . strtoupper($req->search) . '%')
      ->orWhere(DB::raw('upper(roll_no)'), 'LIKE', '%' . strtoupper($req->search) . '%')
      ->orWhere(DB::raw('upper(first_name)'), 'LIKE', '%' . strtoupper($req->search) . '%')
      ->orWhere(DB::raw('upper(class_name)'), 'LIKE', '%' . strtoupper($req->search) . '%')
      ->orWhere(DB::raw('upper(dob)'), 'LIKE', '%' . strtoupper($req->search) . '%')
      ->orWhere(DB::raw('upper(blood_group_name)'), 'LIKE', '%' . strtoupper($req->search) . '%')
      ->orWhere(DB::raw('upper(category_name)'), 'LIKE', '%' . strtoupper($req->search) . '%')
      ->orWhere(DB::raw('upper(fathers_name)'), 'LIKE', '%' . strtoupper($req->search) . '%')
      ->orWhere(DB::raw('upper(fathers_mob_no)'), 'LIKE', '%' . strtoupper($req->search) . '%')
      ->orWhere(DB::raw('upper(section_name)'), 'LIKE', '%' . strtoupper($req->search) . '%');
  }

  /*Read all Active Records*/
  public function active()
  {
    return Student::select(
      DB::raw("admission_no,roll_no,first_name,class_name,section_name,
      CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
        WHEN status = '2' THEN 'Deactivated'
        WHEN status = '3' THEN 'Deactivated'
        END as status,
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time
	  ")
    )
      ->where('status', 1)
      ->orderByDesc('id')
      ->get();
  }
}
