<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Student\Student;
use App\Models\Admin\User;

use Illuminate\Support\Facades\DB;
use Validator;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/*=================================================== Student =========================================================
Created By : Lakshmi kumari 
Created On : 19-May-2023 
Code Status : Open 
*/
class StudentController extends Controller
{

    //for bll 
    public function searchStudentByAdmission(Request $req)
    {
        // echo 'testing controller';die;
        try {
            $data = array();
            $validator = Validator::make($req->all(), [
                'admissionNo'=>'required|string'
            ]);   
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json([
                    'error' => $errors
                ], 400);
            }
            if ($validator->passes()) {
                $searchAdmNo = new Student();
                $data  = $searchAdmNo->readStudentGroup($req);
                $mDeviceId = $req->deviceId ?? "";
                return responseMsgs(true, "Records", $data, "API_ID_132","", "146ms", "post", $mDeviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_132","", "", "post", $mDeviceId);
        } 

    }
    //end

    /**
     *  @OA\Post(
     *  path="/add_student",
     *  tags={"Student"},
     *  summary="Add Student",
     *  operationId="addStudent",     
     *  @OA\Parameter(name="admissionNo",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="rollNo",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="firstName",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="middleName",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="lastName",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="classId",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="className",in="query",required=true,@OA\Schema(type="string",example="")),  
     *  @OA\Parameter(name="sectionId",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="sectionName",in="query",required=true,@OA\Schema(type="string",example="")),  
     *  @OA\Parameter(name="Email",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="Mobile",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="admissionDate",in="query",required=true,@OA\Schema(type="date",example="")),
     *  @OA\Parameter(name="Dob",in="query",required=true,@OA\Schema(type="date",example="")),  
     *  @OA\Parameter(name="aadharNo",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="disability",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="gender_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="gender_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="category_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="category_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="blood_group_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="blood_group_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="caste_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="caste_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="religion_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="religion_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="house_ward_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="house_ward_name",in="query",required=true,@OA\Schema(type="string",example="")),       
     *  @OA\Parameter(name="lastSchoolName",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="lastSchoolAddress",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\RequestBody(required=false,@OA\MediaType(mediaType="multipart/form-data",
     *  @OA\Schema(@OA\Property(property="uploadImage",description="upload image",type="file",format="binary")))),   
     *   
     *  @OA\Parameter(name="admissionMidSession",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="admissionMonth",in="query",required=true,@OA\Schema(type="string",example="")),
     *  
     * @OA\Parameter(name="fathersName",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="fathers_qualification_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="fathers_qualification_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="fathers_occupation_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="fathers_occupation_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="fathersEmail",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="fathersAadhar",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="fathersImage",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="fathersAnnualIncome",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  
     *  @OA\Parameter(name="mothersName",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="mothersMobNo",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="mothers_qualification_id",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="mothers_qualification_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="mothers_occupation_id",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="mothers_occupation_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="mothersEmail",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="mothersAadhar",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="mothersImage",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="mothersAnnualIncome",in="query",required=false,@OA\Schema(type="string",example="")),
     * 
     *  @OA\Parameter(name="guardianName",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="guardianMobNo",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="guardian_qualification_id",in="query",required=false,@OA\Schema(type="integer",example=" ")),
     *  @OA\Parameter(name="guardian_qualification_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="guardian_occupation_id",in="query",required=false,@OA\Schema(type="integer",example=" ")),
     *  @OA\Parameter(name="guardian_occupation_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="guardianEmail",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="guardianAadhar",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="guardianImage",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="guardianAnnualIncome",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="guardian_relation_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="guardian_relation_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="pAddress1",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="pAddress2",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="pLocality",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="pLandmark",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_country_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="p_country_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_state_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="p_state_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_district_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="p_district_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="pPincode",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="cAddress1",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="cAddress2",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="cLocality",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="cLandmark",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_country_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="c_country_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_state_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="c_state_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_district_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="c_district_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="cPincode",in="query",required=false,@OA\Schema(type="integer",example="")),
     * 
     *  @OA\Parameter(name="Hobbies",in="query",required=false,@OA\Schema(type="string",example="")),
     *  
     *  @OA\Parameter(name="bank_id",in="query",required=false,@OA\Schema(type="integer",example=" ")),
     *  @OA\Parameter(name="bank_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="accountNo",in="query",required=false,@OA\Schema(type="integer",example=" ")),
     *  @OA\Parameter(name="account_type",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="ifscCode",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="branchName",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="isTransport",in="query",required=false,@OA\Schema(type="integer",example=" ")),
     * 
     *  @OA\Parameter(name="routeId",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="routeName",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="pickUpPointName",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="busNo",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="applicableFrom",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  
     *  @OA\Response(response=201,description="Success",@OA\MediaType(mediaType="application/json",)),
     *  @OA\Response(response=401,description="Unauthenticated"),
     *  @OA\Response(response=400,description="Bad Request"),
     *  @OA\Response(response=404,description="not found"),     
     *)
    **/

    public function addStudent(Request $req){
        //Description: add students basic details 
        try {
            $data = array();
            $validator = Validator::make($req->all(), [
            'rollNo'                        => 'required|string',
            'classId'                     => 'required|integer',
            'className'                   => 'required|string|max:20', 
            'sectionId'                     => 'required|integer',
            'sectionName'                   => 'required|string|max:20', 
            'firstName'                    => 'required|string|max:20',
            'middleName'                   => 'string|max:20',
            'lastName'                     => 'required|string|max:20',            
            'Email'                         => 'string|email|max:255',
            'Mobile'                        => 'required|numeric|digits:10|regex:/[0-9]/',
            'Dob'                           => 'required|date',
            'aadharNo'                     => 'required|numeric|digits:12',
            'disability'                    => 'required|string|max:10',
            'gender_id'                     => 'required|integer',
            'gender_name'                   => 'required|string|max:20',            
            'category_id'                   => 'required|integer',
            'category_name'                 => 'required|string|max:100',
            'caste_id'                   => 'required|integer',
            'caste_name'                 => 'required|string|max:100',
            'blood_group_id'                => 'required|integer',
            'blood_group_name'              => 'required|string|max:10',
            'religion_id'                 => 'required|integer',
            'religion_name'               => 'required|string|max:50',
            'house_ward_id'              => 'required|integer',
            'house_ward_name'            => 'required|string|max:50',
            // 'upload_image'                  => 'string|upload_image|mimes:jpg,png,jpeg|max:255',
            // 'p_address1'                    => 'string|max:255',
            // 'p_address2'                    => 'string|max:255',
            // 'p_locality'                    => 'string|max:255',
            // 'p_landmark'                    => 'string|max:255',
            // 'p_country'                     => 'string|max:50',
            // 'p_state'                       => 'string|max:50',
            // 'p_district'                    => 'string|max:50',
            // 'p_pincode'                     => 'numeric|digits:6',
            // 'c_address1'                    => 'string|max:255',
            // 'c_address2'                    => 'string|max:255',
            // 'c_locality'                    => 'string|max:255',
            // 'c_landmark'                    => 'string|max:255',
            // 'c_country'                     => 'string|max:50',
            // 'c_state'                       => 'string|max:50',
            // 'c_district'                    => 'string|max:50',
            // 'c_pincode'                     => 'numeric|digits:6',
            // 'fathers_name'                  => 'string|max:50',
            // 'fathers_qualification_name'    => 'string|max:50',
            // 'fathers_occupation_name'       => 'string|max:50',
            // // 'fathers_annual_income'         => 'string|max:10',
            // 'mothers_name'                  => 'string|max:50',
            // 'mothers_qualification_name'    => 'string|max:50',
            // 'mothers_occupation_name'       => 'string|max:50',
            // // 'mothers_annual_income'         => 'string|max:10',
            // 'bank_name'                     => 'string|max:50',
            // 'account_no'                    => 'numeric',
            // 'account_type'                  => 'string|max:20',
            // 'ifsc_code'                     => 'string|max:20',
            // 'branch_name'                   => 'string|max:50'
           
            ]); 
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json([
                    'error' => $errors
                ], 400);
            }
            if ($validator->passes()) {
                // DB::beginTransaction(); 
                $mObject = new Student();
                $data = $mObject->insertData($req);
                $mDeviceId = $req->deviceId ?? ""; 
                return responseMsgs(true, "Records added successfully", $data, "API_ID_130","", "282ms", "post", $mDeviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_130","", "", "post", $mDeviceId);
            // DB::rollBack();
        } 
    }


    /**
     * @OA\Post(
     * path="/search_addmission_no",
     * tags={"Student"},
     * summary="Search Student",
     * operationId="searchStdByAdmNo",
     * @OA\Parameter(name="admissionNo",in="query",required=true,@OA\Schema(type="string",example="123")),
     * @OA\Response(response=200, description="Success",@OA\JsonContent(
     *    @OA\Property(property="status", type="integer", example=""),
     *    @OA\Property(property="data",type="object")
     *  )))
    **/
    public function searchStdByAdmNo(Request $req){ 
        //Description: Get records by id
        try {
            $data = array();
            $validator = Validator::make($req->all(), [
                'admissionNo'=>'required|string'
            ]);   
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json([
                    'error' => $errors
                ], 400);
            }
            if ($validator->passes()) {
                $searchAdmNo = new Student();
                $data1  = $searchAdmNo->searchAdmNo($req);
                $msg = $data1['message'];
                $data = $data1;
                // $data = $data1['emp_id'];
                $mDeviceId = $req->deviceId ?? "";
                return responseMsgs(true, $msg, $data, "API_ID_132","", "146ms", "post", $mDeviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_132","", "", "post", $mDeviceId);
        }     
    }

    /**
     * @OA\Get(
     *    path="/view_student",
     *    operationId="viewStudent",
     *    tags={"Student"},
     *    summary="View student",
     *    description="View Employee",           
     *     @OA\Response(
     *          response=200, description="Success",
     *          @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="200"),
     *             @OA\Property(property="data",type="object")
     *          )
     *       )
     *  )
    */ 
    public function viewStudent(){
        //Description: Get all records 
        try {
            $data = Student::list(); 
            $mDeviceId = $req->deviceId ?? "";
            return responseMsgs(true, "View all records", $data, "API_ID_131","", "186ms", "get", $mDeviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_131","", "", "get", $mDeviceId);
        }
    }

}
