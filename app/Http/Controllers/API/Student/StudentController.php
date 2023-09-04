<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Haruncpi\LaravelIdGenerator\IdGenerator;

use App\Http\Traits\CustomTraits;
use App\Models\Master\ClassMaster;
use App\Models\Master\FinancialYear;
use App\Models\Master\SectionGroupMap;
use App\Models\Master\Section;
use App\Models\Student\Student;
use App\Models\Student\StudentTransport;
use App\Models\Student\StudentSibling;
use App\Models\Sms\Sms;
use Illuminate\Support\Facades\Auth;

use Exception;
use Validator;
use DB;



/*
Created By : Lakshmi kumari  
Created On : 19-May-2023 
Code Status : Open 
*/

class StudentController extends Controller
{
    private $_mStudents;
    private $_tCustome;
    private $_mStudentTransport;
    private $_mStudentSibling;
    private $_mSms;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mStudents = new Student();
        $this->_tCustome = new CustomTraits();
        $this->_mStudentTransport = new StudentTransport();
        $this->_mStudentSibling = new StudentSibling();
        $this->_mSms = new Sms();
    }

    /**---------------------------------------------------------------------------------------------
     * |For BLL 
     */
    public function searchStudentByAdmission(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'admissionNo' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $searchAdmNo = new Student();
            $data  = $searchAdmNo->readStudentGroup($req->admissionNo);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Records", $data, "", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //add request
    public function store(Request $req)
    {
        //Description: add students basic details 
        $validator = Validator::make($req->all(), [
            'admissionNo'                  => 'required|string',
            'rollNo'                       => 'required',
            'classId'                      => 'required|integer',
            'sectionId'                    => 'required|integer',
            'firstName'                    => 'required|string|max:20',
            'lastName'                     => 'required|string|max:20',
            'Email'                        => 'string|email|max:255',
            'admissionDate'                => 'required|date',
            'Mobile'                       => 'required|numeric',
            'Dob'                          => 'required|date',
            'aadharNo'                     => 'required|numeric|digits:12',
            'disability'                   => 'required|string|max:10',
            'genderName'                   => 'required|string|max:20',
            'categoryName'                 => 'required|string|max:100',
            'casteName'                    => 'required|string|max:100',
            'bloodGroupName'               => 'required|string|max:10',
            'religionName'                 => 'required|string|max:50',
            'houseWardName'                => 'required|string|max:50'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            DB::beginTransaction();
            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));
            $isExists = $this->_mStudents->readStudentGroup($req);
            if (collect($isExists)->isNotEmpty())
                throw new Exception("Student already existing");

            $result = array();
            $stdImg = '';
            $fatherImg = '';
            $motherImg = '';
            $guardianImg = '';

            if ($req->uploadImage != "") {
                $uploadImage = $req->uploadImage;
                $get_file_name = $req->admissionNo . '-' . $req->uploadImage->getClientOriginalName();
                $path = public_path('school/students/');
                $stdImg = 'school/students/' . $req->admissionNo . '-' . $uploadImage->getClientOriginalName();
                $req->file('uploadImage')->move($path, $get_file_name);
            }

            if ($req->guardianImage != "") {
                $uploadImage = $req->guardianImage;
                $get_file_name = $req->admissionNo . '-' . $req->uploadImage->getClientOriginalName();
                $path = public_path('school/students/');
                $guardianImg = 'school/students/' . $req->admissionNo . '-' . $uploadImage->getClientOriginalName();
                $req->file('guardianImage')->move($path, $get_file_name);
            }

            if ($req->mothersImage != "") {
                $uploadImage = $req->mothersImage;
                $get_file_name = $req->admissionNo . '-' . $req->uploadImage->getClientOriginalName();
                $path = public_path('school/students/');
                $motherImg = 'school/students/' . $req->admissionNo . '-' . $uploadImage->getClientOriginalName();
                $req->file('mothersImage')->move($path, $get_file_name);
            }

            if ($req->fathersImage != "") {
                $uploadImage = $req->fathersImage;
                $get_file_name = $req->admissionNo . '-' . $req->uploadImage->getClientOriginalName();
                $path = public_path('school/students/');
                $fatherImg = 'school/students/' . $req->admissionNo . '-' . $uploadImage->getClientOriginalName();
                $req->file('fathersImage')->move($path, $get_file_name);
            }

            //======================
            $username = $this->generateUsername($req['firstName'], $req['fathersName'], $req['admissionNo']);
            //=========================

            $this->_mStudents->admission_no = $req['admissionNo'];
            $this->_mStudents->roll_no = $req['rollNo'];
            $this->_mStudents->first_name = $req['firstName'];
            $this->_mStudents->middle_name = $req['middleName'];
            $this->_mStudents->last_name = $req['lastName'];
            $this->_mStudents->class_id = $req['classId'];
            $this->_mStudents->class_name = $req['className'];
            $this->_mStudents->section_id = $req['sectionId'];
            $this->_mStudents->section_name = $req['sectionName'];
            $this->_mStudents->dob = $req['Dob'];
            $this->_mStudents->admission_date = $req['admissionDate'];
            $this->_mStudents->gender_id = $req['genderId'];
            $this->_mStudents->gender_name = $req['genderName'];
            $this->_mStudents->blood_group_id = $req['bloodGroupId'];
            $this->_mStudents->blood_group_name = $req['bloodGroupName'];
            $this->_mStudents->email = $req['Email'];
            $this->_mStudents->mobile = $req['Mobile'];
            $this->_mStudents->aadhar_no = $req['aadharNo'];
            $this->_mStudents->disability = $req['disability'];
            $this->_mStudents->category_id = $req['categoryId'];
            $this->_mStudents->category_name = $req['categoryName'];
            $this->_mStudents->caste_id = $req['casteId'];
            $this->_mStudents->caste_name = $req['casteName'];
            $this->_mStudents->religion_id = $req['religionId'];
            $this->_mStudents->religion_name = $req['religionName'];
            $this->_mStudents->house_ward_id = $req['houseWardId'];
            $this->_mStudents->house_ward_name = $req['houseWardName'];
            $this->_mStudents->upload_image = $stdImg;
            $this->_mStudents->last_school_name = $req['lastSchoolName'];
            $this->_mStudents->last_school_address = $req['lastSchoolAddress'];
            $this->_mStudents->admission_mid_session = $req['admissionMidSession'];
            $this->_mStudents->admission_month = $req['admissionMonth'];
            $this->_mStudents->fathers_name = $req['fathersName'];
            $this->_mStudents->fathers_mob_no = $req['fathersMobNo'];
            $this->_mStudents->fathers_qualification_id = $req['fathersQualificationId'];
            $this->_mStudents->fathers_qualification_name = $req['fathersQualificationName'];
            $this->_mStudents->fathers_occupation_id = $req['fathersOccupationId'];
            $this->_mStudents->fathers_occupation_name = $req['fathersOccupationName'];
            $this->_mStudents->fathers_email = $req['fathersEmail'];
            $this->_mStudents->fathers_aadhar = $req['fathersAadhar'];
            $this->_mStudents->fathers_image = $fatherImg;
            $this->_mStudents->fathers_annual_income = $req['fathersAnnualIncome'];
            $this->_mStudents->mothers_name = $req['mothersName'];
            $this->_mStudents->mothers_mob_no = $req['mothersMobNo'];
            $this->_mStudents->mothers_qualification_id = $req['mothersQualificationId'];
            $this->_mStudents->mothers_qualification_name = $req['mothersQualificationName'];
            $this->_mStudents->mothers_occupation_id = $req['mothersOccupationId'];
            $this->_mStudents->mothers_occupation_name = $req['mothersOccupationName'];
            $this->_mStudents->mothers_email = $req['mothersEmail'];
            $this->_mStudents->mothers_aadhar = $req['mothersAadhar'];
            $this->_mStudents->mothers_image = $motherImg;
            $this->_mStudents->mothers_annual_income = $req['mothersAnnualIncome'];
            $this->_mStudents->guardian_name = $req['guardianName'];
            $this->_mStudents->guardian_mob_no = $req['guardianMobNo'];
            $this->_mStudents->guardian_qualification_id = $req['guardianQualificationId'];
            $this->_mStudents->guardian_qualification_name = $req['guardianQualificationName'];
            $this->_mStudents->guardian_occupation_id = $req['guardianOccupationId'];
            $this->_mStudents->guardian_occupation_name = $req['guardianOccupationName'];
            $this->_mStudents->guardian_email = $req['guardianEmail'];
            $this->_mStudents->guardian_aadhar = $req['guardianAadhar'];
            $this->_mStudents->guardian_image = $guardianImg;
            $this->_mStudents->guardian_annual_income = $req['guardianAnnualIncome'];
            // $this->_mStudents->guardian_relation = $req['guardian_relation'];
            $this->_mStudents->p_address1 = $req['pAddress1'];
            $this->_mStudents->p_address2 = $req['pAddress2'];
            $this->_mStudents->p_locality = $req['pLocality'];
            $this->_mStudents->p_landmark = $req['pLandmark'];
            $this->_mStudents->p_country_id = $req['pCountryId'];
            $this->_mStudents->p_country_name = $req['pCountryName'];
            $this->_mStudents->p_state_id = $req['pStateId'];
            $this->_mStudents->p_state_name = $req['pStateName'];
            $this->_mStudents->p_district_id = $req['pDistrictId'];
            $this->_mStudents->p_district_name = $req['pDistrictName'];
            $this->_mStudents->p_pincode = $req['pPincode'];
            $this->_mStudents->c_address1 = $req['cAddress1'];
            $this->_mStudents->c_address2 = $req['cAddress2'];
            $this->_mStudents->c_locality = $req['cLocality'];
            $this->_mStudents->c_landmark = $req['cLandmark'];
            $this->_mStudents->c_country_id = $req['cCountryId'];
            $this->_mStudents->c_country_name = $req['cCountryName'];
            $this->_mStudents->c_state_id = $req['cStateId'];
            $this->_mStudents->c_state_name = $req['cStateName'];
            $this->_mStudents->c_district_id = $req['cDistrictId'];
            $this->_mStudents->c_district_name = $req['cDistrictName'];
            $this->_mStudents->c_pincode = $req['cPincode'];
            $this->_mStudents->hobbies = $req['Hobbies'];
            $this->_mStudents->bank_id = $req['bank_id'];
            $this->_mStudents->bank_name = $req['bank_name'];
            $this->_mStudents->account_no = $req['accountNo'];
            $this->_mStudents->ifsc_code = $req['ifscCode'];
            $this->_mStudents->branch_name = $req['branchName'];
            $this->_mStudents->is_transport = $req['isTransport'];
            $this->_mStudents->academic_year =   $fy;
            $this->_mStudents->ip_address = getClientIpAddress();
            $this->_mStudents->created_by   = authUser()->id;
            $this->_mStudents->school_id = authUser()->school_id;
            $this->_mStudents->password = Hash::make('2020');
            $this->_mStudents->user_password = Hash::make('2020');
            $this->_mStudents->user_name =   $username;

            // print_var($this->_mStudents);
            // die;
            $this->_mStudents->save();

            $mStudentTransport = "";
            //insert single data and multi data for student transport
            if ($req['isTransport'] == true) {
                $mStudentTransport = new StudentTransport();
                //$insert = [
                $mStudentTransport->std_tbl_id = $this->_mStudents->id;
                $mStudentTransport->route_id = $req['routeId'];
                $mStudentTransport->pick_up_point_id = $req['pickUpPointId'];
                $mStudentTransport->academic_year = $fy;
                // $mStudentTransport->school_id = authUser()->school_id,
                // $mStudentTransport->created_by = authUser()->id,
                $mStudentTransport->ip_address = getClientIpAddress();
                $mStudentTransport->created_by   = authUser()->id;
                $mStudentTransport->school_id = authUser()->school_id;

                // print_var($mStudentTransport);
                $mStudentTransport->save();
            }

            //insert single data and multi data for student sibling
            $mStudentSibling = "";
            if ($req['siblingDetails'] != "") {
                foreach ($req['siblingDetails'] as $key => $ob) {

                    $mStudentSibling = new StudentSibling();
                    // $insert = [
                    $mStudentSibling->std_tbl_id = $this->_mStudents->id;
                    $mStudentSibling->sibling_name = $ob['siblingName'];
                    $mStudentSibling->sibling_admission_no = $ob['siblingAdmissionNo'];
                    $mStudentSibling->sibling_roll_no = $ob['siblingRollNo'];
                    $mStudentSibling->class_id = $ob['siblingClass'];
                    $mStudentSibling->section_id = $ob['siblingSection'];
                    $mStudentSibling->academic_year = $fy;
                    // $mStudentSibling->school_id = authUser()->school_id,
                    // $mStudentSibling->created_by = authUser()->id,
                    $mStudentSibling->ip_address = getClientIpAddress();
                    $mStudentSibling->created_by   = authUser()->id;
                    $mStudentSibling->school_id = authUser()->school_id;

                    // print_var($mEmpEduObject);
                    $mStudentSibling->save();

                    // $mStudentSibling["std_tbl_id"] =  $this->_mStudents->id;
                    // $mStudentSibling["sibling_name"] =  $ob['siblingName'];
                    // $mStudentSibling["sibling_class"] =  $ob['siblingClass'];
                    // $mStudentSibling["sibling_section"] =  $ob['siblingSection'];
                    // $mStudentSibling["siblingAdmissionNo"] =  $ob['sibling_admission_no'];
                    // $mStudentSibling["sibling_roll_no"] =  $ob['siblingRollNo'];
                    // $mStudentSibling["academic_year"] =  $fy;
                    // $mStudentSibling["school_id"] =  authUser()->school_id;
                    // $mStudentSibling["created_by"] =  authUser()->id;
                    // $mStudentSibling["ip_address"] =  getClientIpAddress();
                    // $stdSiblingData = $this->_mStudentSibling->insertData($mStudentSibling);
                    // print_var($stdTransportata->id);
                }
            }
            // DB::rollback();
            DB::commit();
            // dd();
            $result['basicDetails'] = $this->_mStudents;
            $result['transportDetails'] = $mStudentTransport;
            $result['siblingDetails'] = $mStudentSibling;
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Student Registration Done Successfully", $result, "API_4.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.1", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //Update Student table
    public function edit(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id'                           => 'required|numeric',
            'admissionNo'                  => 'required',
            'rollNo'                       => 'required',
            'classId'                      => 'required|integer',
            'sectionId'                    => 'required|integer',
            'firstName'                    => 'required|string|max:20',
            'lastName'                     => 'required|string|max:20',
            'Email'                        => 'string|email|max:255',
            'admissionDate'                => 'required|date',
            'Mobile'                       => 'required|numeric',
            'Dob'                          => 'required|date',
            'aadharNo'                     => 'required|numeric|digits:12',
            'disability'                   => 'required|string|max:10',
            'genderName'                   => 'required|string|max:20',
            'categoryName'                 => 'required|string|max:100',
            'casteName'                    => 'required|string|max:100',
            'bloodGroupName'               => 'required|string|max:10',
            'religionName'                 => 'required|string|max:50',
            'houseWardName'                => 'required|string|max:50'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            $isExists = $this->_mStudents->readStudentGroup($req->admissionNo);
            if ($isExists && $isExists->where('id', '!=', $req->id)->isNotEmpty())
                throw new Exception("Student Already Existing");
            $getData = $this->_mStudents::findOrFail($req->id);

            // $this->_mStudents-> admission_no = $req['admissionNo'];
            $this->_mStudents->roll_no = $req['rollNo'];
            $this->_mStudents->first_name = $req['firstName'];
            $this->_mStudents->middle_name = $req['middleName'];
            $this->_mStudents->last_name = $req['lastName'];
            $this->_mStudents->class_id = $req['classId'];
            $this->_mStudents->class_name = $req['className'];
            $this->_mStudents->section_id = $req['sectionId'];
            $this->_mStudents->section_name = $req['sectionName'];
            $this->_mStudents->dob = $req['Dob'];
            $this->_mStudents->admission_date = $req['admissionDate'];
            $this->_mStudents->gender_id = $req['genderId'];
            $this->_mStudents->gender_name = $req['genderName'];
            $this->_mStudents->blood_group_id = $req['bloodGroupId'];
            $this->_mStudents->blood_group_name = $req['bloodGroupName'];
            $this->_mStudents->email = $req['Email'];
            $this->_mStudents->mobile = $req['Mobile'];
            $this->_mStudents->aadhar_no = $req['aadharNo'];
            $this->_mStudents->disability = $req['disability'];
            $this->_mStudents->category_id = $req['categoryId'];
            $this->_mStudents->category_name = $req['categoryName'];
            $this->_mStudents->caste_id = $req['casteId'];
            $this->_mStudents->caste_name = $req['casteName'];
            $this->_mStudents->religion_id = $req['religionId'];
            $this->_mStudents->religion_name = $req['religionName'];
            $this->_mStudents->house_ward_id = $req['houseWardId'];
            $this->_mStudents->house_ward_name = $req['houseWardName'];
            $this->_mStudents->upload_image = $req['uploadImage'];
            $this->_mStudents->last_school_name = $req['lastSchoolName'];
            $this->_mStudents->last_school_address = $req['lastSchoolAddress'];
            $this->_mStudents->admission_mid_session = $req['admissionMidSession'];
            $this->_mStudents->admission_month = $req['admissionMonth'];
            $this->_mStudents->fathers_name = $req['fathersName'];
            $this->_mStudents->fathers_mob_no = $req['fathersMobNo'];
            $this->_mStudents->fathers_qualification_id = $req['fathersQualificationId'];
            $this->_mStudents->fathers_qualification_name = $req['fathersQualificationName'];
            $this->_mStudents->fathers_occupation_id = $req['fathersOccupationId'];
            $this->_mStudents->fathers_occupation_name = $req['fathersOccupationName'];
            $this->_mStudents->fathers_email = $req['fathersEmail'];
            $this->_mStudents->fathers_aadhar = $req['fathersAadhar'];
            $this->_mStudents->fathers_image = '';
            $this->_mStudents->fathers_annual_income = $req['fathersAnnualIncome'];
            $this->_mStudents->mothers_name = $req['mothersName'];
            $this->_mStudents->mothers_mob_no = $req['mothersMobNo'];
            $this->_mStudents->mothers_qualification_id = $req['mothersQualificationId'];
            $this->_mStudents->mothers_qualification_name = $req['mothersQualificationName'];
            $this->_mStudents->mothers_occupation_id = $req['mothersOccupationId'];
            $this->_mStudents->mothers_occupation_name = $req['mothersOccupationName'];
            $this->_mStudents->mothers_email = $req['mothersEmail'];
            $this->_mStudents->mothers_aadhar = $req['mothersAadhar'];
            $this->_mStudents->mothers_image = '';
            $this->_mStudents->mothers_annual_income = $req['mothersAnnualIncome'];
            $this->_mStudents->guardian_name = $req['guardianName'];
            $this->_mStudents->guardian_mob_no = $req['guardianMobNo'];
            $this->_mStudents->guardian_qualification_id = $req['guardianQualificationId'];
            $this->_mStudents->guardian_qualification_name = $req['guardianQualificationName'];
            $this->_mStudents->guardian_occupation_id = $req['guardianOccupationId'];
            $this->_mStudents->guardian_occupation_name = $req['guardianOccupationName'];
            $this->_mStudents->guardian_email = $req['guardianEmail'];
            $this->_mStudents->guardian_aadhar = $req['guardianAadhar'];
            $this->_mStudents->guardian_image = '';
            $this->_mStudents->guardian_annual_income = $req['guardianAnnualIncome'];
            // $this->_mStudents->guardian_relation = $req['guardian_relation'];
            $this->_mStudents->p_address1 = $req['pAddress1'];
            $this->_mStudents->p_address2 = $req['pAddress2'];
            $this->_mStudents->p_locality = $req['pLocality'];
            $this->_mStudents->p_landmark = $req['pLandmark'];
            $this->_mStudents->p_country_id = $req['pCountryId'];
            $this->_mStudents->p_country_name = $req['pCountryName'];
            $this->_mStudents->p_state_id = $req['pStateId'];
            $this->_mStudents->p_state_name = $req['pStateName'];
            $this->_mStudents->p_district_id = $req['pDistrictId'];
            $this->_mStudents->p_district_name = $req['pDistrictName'];
            $this->_mStudents->p_pincode = $req['pPincode'];
            $this->_mStudents->c_address1 = $req['cAddress1'];
            $this->_mStudents->c_address2 = $req['cAddress2'];
            $this->_mStudents->c_locality = $req['cLocality'];
            $this->_mStudents->c_landmark = $req['cLandmark'];
            $this->_mStudents->c_country_id = $req['cCountryId'];
            $this->_mStudents->c_country_name = $req['cCountryName'];
            $this->_mStudents->c_state_id = $req['cStateId'];
            $this->_mStudents->c_state_name = $req['cStateName'];
            $this->_mStudents->c_district_id = $req['cDistrictId'];
            $this->_mStudents->c_district_name = $req['cDistrictName'];
            $this->_mStudents->c_pincode = $req['cPincode'];
            $this->_mStudents->hobbies = $req['Hobbies'];
            $this->_mStudents->bank_id = $req['bank_id'];
            $this->_mStudents->bank_name = $req['bank_name'];
            $this->_mStudents->account_no = $req['accountNo'];
            $this->_mStudents->ifsc_code = $req['ifscCode'];
            $this->_mStudents->branch_name = $req['branchName'];
            $this->_mStudents->is_transport = $req['isTransport'];

            $this->_mStudents->version_no = $getData->version_no + 1;
            $this->_mStudents->updated_at = Carbon::now();

            if (isset($req->status)) {                  // In Case of Deactivation or Activation 
                $status = $req->status == 'deactive' ? 0 : 1;
                $this->_mStudents->status = $status;
            }
            $data = $this->_mStudents;
            $this->_mStudents->update();
            // $feeHead = $this->_mStudents::findOrFail($req->id);
            // $feeHead->update($metaReqs);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Records Updated Successfully", [$data], "API_4.2", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.2", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //Get Student Group By Id get student profile
    public function showStudentProfile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $show = $this->_mStudents->getGroupById($req->id);
            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Records", $show, "API_4.3", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.3", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }


    //Get Student Group By Id get student profile
    public function showParentsProfile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $show = $this->_mStudents->getParentGroupById($req->id);
            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Records", $show, "API_4.17", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.17", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //show classmates
    //Get Student Group By Id get student profile
    public function showClassMates(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $mStudents = Student::where('id', $req->id)
                ->where('status', 1)
                ->first();
            $classId = $mStudents->class_id;

            $show = $this->_mStudents->getclassMatesGroupById($req, $classId);
            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Records", $show, "API_4.18", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.18", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }



    //search by name
    public function search(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'search' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $getData = $this->_mStudents->searchByName($req);
            $perPage = $req->perPage ? $req->perPage : 10;
            $paginater = $getData->paginate($perPage);
            // if ($paginater == "")
            //     throw new Exception("Data Not Found");
            $list = [
                "current_page" => $paginater->currentPage(),
                "perPage" => $perPage,
                "last_page" => $paginater->lastPage(),
                "data" => $paginater->items(),
                "total" => $paginater->total()
            ];
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Searched Records", $list, "API_4.4", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.4", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //Retrieve All
    public function retrieveAll(Request $req)
    {
        try {
            $getData = $this->_mStudents->retrieveAll();
            $perPage = $req->perPage ? $req->perPage : 10;
            $paginater = $getData->paginate($perPage);
            $list = [
                "current_page" => $paginater->currentPage(),
                "perPage" => $perPage,
                "last_page" => $paginater->lastPage(),
                "data" => collect($paginater->items())->map(function ($val) {
                    $path = "getImageLink?path=";
                    $val->ebook_docs = trim($val->ebook_docs) ? ($path . $val->ebook_docs) : "";
                    $val->cover_pic_docs = trim($val->cover_pic_docs) ? ($path . $val->cover_pic_docs) : "";
                    return $val;
                }),
                "total" => $paginater->total()
            ];
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Records", $list, "API_4.5", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.5", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //Delete
    public function delete(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'status' => 'required|in:active,deactive'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            if (isset($req->status)) {                  // In Case of Deactivation or Activation 
                $status = $req->status == 'deactive' ? 0 : 1;
                $metaReqs =  [
                    'status' => $status
                ];
            }
            $std = $this->_mStudents::findOrFail($req->id);
            $std->update($metaReqs);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Changes Done Successfully", $req->status, "API_4.6", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.6", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //Active All
    public function activeAll(Request $req)
    {
        try {
            $getData = $this->_mStudents->active();
            if (collect($getData)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Active Records", $getData, "API_4.7", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.7", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //search by admission no
    public function searchStdByAdmNo(Request $req)
    {
        //Description: Get records by id
        $validator = Validator::make($req->all(), [
            'admissionNo' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $msg = '';
            $data = Student::select('id')
                ->where([['admission_no', '=', $req->admissionNo], ['status', '=', '1']])->first();
            if ($data != "") {
                $msg = "Student ID Already Existing";
                $data1 = $data;
            } else {
                $msg = "Student Not Found";
                $data1 = ['admission_no' => $req->admissionNo, 'message' => 'Admission No. not found', 'value' => 'false'];
            }
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Searched Records", $data1, "API_4.8", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.8", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //add bulk data using csv
    public function storeCSV(Request $req)
    {
        //validation
        $validator = Validator::make($req->all(), [
            'uploadCSV' => 'required|mimes:csv|max:2048'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $file = $req->file('uploadCSV');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            $valid_extension = array("csv"); // Valid File Extensions            
            $maxFileSize = 2097152; // 2MB in Bytes

            // Check file extension & Check file size
            if (in_array(strtolower($extension), $valid_extension)) {
                if ($fileSize <= $maxFileSize) {
                    $location = 'uploads'; // File upload location                    
                    $file->move($location, $filename); // Upload file                    
                    $filepath = public_path($location . "/" . $filename); // Import CSV to Database
                    $password = Hash::make('2020');
                    $file = fopen($filepath, "r"); // Reading file
                    $importData_arr = array();
                    $i = 0;
                    while (($filedata = fgetcsv($file, 4096, ",")) !== FALSE) {
                        $num = count($filedata);
                        // Skip first row (Remove below comment if you want to skip the first row)
                        if ($i == 0) {
                            $i++;
                            continue;
                        }
                        for ($c = 0; $c < $num; $c++) {
                            $importData_arr[$i][] = $filedata[$c];
                        }
                        $i++;
                    }
                    fclose($file);

                    foreach ($importData_arr as $data) {
                        $className = $data[4]; // getting class id...
                        $classObj = ClassMaster::where('class_name', $className)->firstOrFail();
                        // $classObj = ClassMaster::where(DB::raw('upper(class_name)'), 'LIKE', '%' . strtoupper($className) . '%')->firstOrFail();
                        $classId = $classObj->id;

                        $sectionName = $data[9]; // getting section id...
                        $sectionObj = Section::where('section_name', $sectionName)->firstOrFail();
                        $sectionId = $sectionObj->id;

                        $genderId = null; // getting gender id...
                        $genderName = $data[5];
                        if ($genderName == 'M') {
                            $genderId = 1;
                            $genderName = 'male';
                        } elseif ($genderName == 'F') {
                            $genderId = 2;
                            $genderName = 'female';
                        } else {
                            $genderId = 3;
                            $genderName = 'other';
                        }

                        $yr = explode('/', $data[1]);

                        $yrObj = FinancialYear::where('abbr', $yr[0])->firstOrFail();  // getting section id...
                        $yrName = $yrObj->fy;

                        $admissionNo = $data[1] . '/' . $data[2];
                        $username = $this->generateUsername($data[0], $data[3], $admissionNo);
                        $insertData = array(
                            // 'roll_no' => $data[2],
                            'first_name' => $data[0],
                            'academic_year' => $yrName,
                            'admission_no' => $admissionNo,
                            'fathers_name' => $data[3],
                            'class_id' => $classId,
                            'class_name' => $data[4],
                            'gender_id' => $genderId,
                            'gender_name' => $genderName,
                            'p_address1' => $data[6],
                            'mobile' => $data[7],
                            'admission_date' => $data[8],
                            'section_name' => $data[9],
                            'section_id' => $sectionId,
                            'created_by' => 1,
                            'ip_address' => getClientIpAddress(),
                            'password' => $password,
                            'user_name' => $username,
                            'user_password' => $password,
                        );
                        // print_var($insertData);
                        // die;                        
                        // Check for duplicate data before inserting
                        $duplicateData = Student::where('admission_no', $admissionNo)
                            ->where('academic_year', $yrName)
                            ->where('status', 1)
                            ->first();
                        if (!$duplicateData) {
                            Student::create($insertData);
                        } else {
                            throw new Exception("Data Already Existing");
                        }
                    }
                }
            }
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Successfully Uploaded", [], "API_4.9", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.9", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //check student status
    public function showStudentGroup(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'classId' => 'required|numeric',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $show = $this->_mStudents->getStudentGroupBySection($req);
            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Records", $show, "API_4.10", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.10", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    // Get Student Id Details...........
    public function getIdCard(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'admissionNo' => 'required|string',
            'classId' => 'required|numeric',
            // 'sectionId' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $mStudents = Student::where('admission_no', $req->admissionNo)
                ->where('status', 1)
                ->first();
            if (collect($mStudents)->isEmpty())
                throw new Exception('Admission no is not existing');
            $studentId  = $mStudents->id;
            $getAll = $this->_mStudents->getStudentIdDetails($req, $studentId);
            if (collect($getAll)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "", $getAll, "API_4.11", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.11", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    // update role
    public function updateRole(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric',
            'roleId' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $getData = $this->_mStudents::findOrFail($req->id);
            $metaReqs = [
                'role_id' => $req->roleId,
                'version_no' => $getData->version_no + 1
            ];
            $metaReqs = array_merge($metaReqs, [
                'json_logs' => trim($getData->json_logs . "," . json_encode($metaReqs), ",")
            ]);
            if (isset($req->status)) {                  // In Case of Deactivation or Activation 
                $status = $req->status == 'deactive' ? 0 : 1;
                $metaReqs = array_merge($metaReqs, [
                    'status' => $status
                ]);
            }
            $getData->update($metaReqs);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Records Updated Successfully", [], "API_4.12", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.12", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //count active students
    public function countActiveStudent(Request $req)
    {
        try {
            $rowCount = $this->_mStudents->countActive();
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Total Count of All Active Students", $rowCount, "API_4.13", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.13", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //change parents password
    public function parentsChangePassword(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'userName' => 'required',
            'password' => 'required',
            'confirmPassword' => 'required',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $data = $this->_mStudents->where('user_name', $req->userName)->first();
            if ($req->password != $req->confirmPassword)
                throw new Exception("Password Not Matched!");
            if (!$data)
                throw new Exception("Record Not Found!");
            if ($data->remember_token != "") {
                $edit = [
                    'user_password' => Hash::make($req->password)
                ];
                $data->update($edit);
            }
            $data1 = ['id' => $data->id, 'name' => $data->first_name, 'admissionNo' => $data->admission_no, 'token' => $data->remember_token, 'token_type' => 'Bearer'];
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Password Changed Successfully", $data1, "API_4.14", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "API_4.14", "", "", "", "", $req->deviceId ?? "");
        }
    }

    //change student password
    public function studentChangePassword(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'admissionNo' => 'required',
            'password' => 'required',
            'confirmPassword' => 'required',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $data = $this->_mStudents->where('admission_no', $req->admissionNo)->first();
            if ($req->password != $req->confirmPassword)
                throw new Exception("Password Not Matched!");
            if (!$data)
                throw new Exception("Record Not Found!");
            if ($data->remember_token != "") {
                $edit = [
                    'password' => Hash::make($req->password)
                ];
                $data->update($edit);
            }
            $data1 = ['id' => $data->id, 'name' => $data->first_name, 'admissionNo' => $data->admission_no, 'token' => $data->remember_token, 'token_type' => 'Bearer'];
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Password Changed Successfully", $data1, "API_4.16", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "API_4.16", "", "", "", "", $req->deviceId ?? "");
        }
    }

    //logout
    public function logout(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $user = $this->_mStudents->where('id', $req->id)->first();
            $user->remember_token = null;
            $user->save();
            $user->tokens()->delete();
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Logout Successfully", "", " API_4.15", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), " API_4.15", "", "", "", "", $req->deviceId ?? "");
        }
    }




    /*------------------------------------outside of middleware--------------------------------*/
    //for online registration 
    public function onlineRegistration(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'schoolId' => 'integer',
            'classId'  => 'required|integer',
            'firstName' => 'required|string',
            'lastName'  => 'required|string',
            'Dob'    => 'required|date',
            'disability'  => 'required|string',
            'genderName'  => 'required|string',
            'categoryName'  => 'required|string',
            'casteName'  => 'required|string',
            'bloodGroupName' => 'required|string',
            'religionName'   => 'required|string',
            'uploadImage'  => 'file|mimes:jpg,png,jpeg',
            'pAddress1'   => 'required|string',
            'pPincode' => 'required|numeric|digits:6'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            DB::beginTransaction();
            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));
            $studentIdGeneration = IdGenerator::generate([
                'table' => 'students',
                'field' => 'admission_no',
                'length' => 20,
                'prefix' => 'REG/' . $fy . '/',
                // 'prefix' => date('Y').'/',
                'reset_on_prefix_change' => true
            ]);

            $isExists = $this->_mStudents->readOnlineStudentGroup($req);
            if (collect($isExists)->isNotEmpty())
                throw new Exception("Student already existing");

            $result = array();
            $stdImg = '';
            $fatherImg = '';
            $motherImg = '';
            $guardianImg = '';
            // $userId =  $this->_tCustome->forOnlineRegUser();
            // $schoolId =  $req->schoolId;

            if ($req->uploadImage != "") {
                $uploadImage = $req->uploadImage;
                $get_file_name = $req->firstName . '-' . $req->uploadImage->getClientOriginalName();
                $path = public_path('school/students/');
                $stdImg = 'school/students/' . $req->firstName . '-' . $uploadImage->getClientOriginalName();
                $req->file('uploadImage')->move($path, $get_file_name);
            }

            //======================
            $username = $this->generateUsername($req['firstName'], $req['fathersName'], $studentIdGeneration);
            //=========================
            $this->_mStudents->first_name = $req['firstName'];
            $this->_mStudents->middle_name = $req['middleName'];
            $this->_mStudents->last_name = $req['lastName'];
            $this->_mStudents->class_id = $req['classId'];
            $this->_mStudents->class_name = $req['className'];
            $this->_mStudents->section_name = $req['sectionName'];
            $this->_mStudents->dob = $req['Dob'];
            $this->_mStudents->gender_name = $req['genderName'];
            $this->_mStudents->blood_group_name = $req['bloodGroupName'];
            $this->_mStudents->email = $req['Email'];
            $this->_mStudents->mobile = $req['Mobile'];
            $this->_mStudents->aadhar_no = $req['aadharNo'];
            $this->_mStudents->disability = $req['disability'];
            $this->_mStudents->category_name = $req['categoryName'];
            $this->_mStudents->caste_name = $req['casteName'];
            $this->_mStudents->religion_name = $req['religionName'];
            $this->_mStudents->upload_image = $stdImg;
            $this->_mStudents->last_school_name = $req['lastSchoolName'];
            $this->_mStudents->last_school_address = $req['lastSchoolAddress'];
            $this->_mStudents->admission_mid_session = $req['admissionMidSession'];
            $this->_mStudents->admission_month = $req['admissionMonth'];
            $this->_mStudents->fathers_name = $req['fathersName'];
            $this->_mStudents->fathers_mob_no = $req['fathersMobNo'];
            $this->_mStudents->fathers_qualification_name = $req['fathersQualificationName'];
            $this->_mStudents->fathers_occupation_name = $req['fathersOccupationName'];
            $this->_mStudents->fathers_email = $req['fathersEmail'];
            $this->_mStudents->fathers_aadhar = $req['fathersAadhar'];
            $this->_mStudents->fathers_image = $fatherImg;
            $this->_mStudents->fathers_annual_income = $req['fathersAnnualIncome'];
            $this->_mStudents->mothers_name = $req['mothersName'];
            $this->_mStudents->mothers_mob_no = $req['mothersMobNo'];
            $this->_mStudents->mothers_qualification_name = $req['mothersQualificationName'];
            $this->_mStudents->mothers_occupation_name = $req['mothersOccupationName'];
            $this->_mStudents->mothers_email = $req['mothersEmail'];
            $this->_mStudents->mothers_aadhar = $req['mothersAadhar'];
            $this->_mStudents->mothers_image = $motherImg;
            $this->_mStudents->mothers_annual_income = $req['mothersAnnualIncome'];
            $this->_mStudents->guardian_name = $req['guardianName'];
            $this->_mStudents->guardian_mob_no = $req['guardianMobNo'];
            $this->_mStudents->guardian_qualification_name = $req['guardianQualificationName'];
            $this->_mStudents->guardian_occupation_name = $req['guardianOccupationName'];
            $this->_mStudents->guardian_email = $req['guardianEmail'];
            $this->_mStudents->guardian_aadhar = $req['guardianAadhar'];
            $this->_mStudents->guardian_image = $guardianImg;
            $this->_mStudents->guardian_annual_income = $req['guardianAnnualIncome'];
            $this->_mStudents->p_address1 = $req['pAddress1'];
            $this->_mStudents->p_address2 = $req['pAddress2'];
            $this->_mStudents->p_locality = $req['pLocality'];
            $this->_mStudents->p_landmark = $req['pLandmark'];
            $this->_mStudents->p_country_id = $req['pCountryId'];
            $this->_mStudents->p_state_id = $req['pStateId'];
            $this->_mStudents->p_district_id = $req['pDistrictId'];
            $this->_mStudents->p_pincode = $req['pPincode'];
            $this->_mStudents->c_address1 = $req['cAddress1'];
            $this->_mStudents->c_address2 = $req['cAddress2'];
            $this->_mStudents->c_locality = $req['cLocality'];
            $this->_mStudents->c_landmark = $req['cLandmark'];
            $this->_mStudents->c_country_id = $req['cCountryId'];
            $this->_mStudents->c_state_id = $req['cStateId'];
            $this->_mStudents->c_district_id = $req['cDistrictId'];
            $this->_mStudents->c_pincode = $req['cPincode'];
            $this->_mStudents->hobbies = $req['Hobbies'];
            $this->_mStudents->bank_id = $req['bank_id'];
            $this->_mStudents->bank_name = $req['bank_name'];
            $this->_mStudents->account_no = $req['accountNo'];
            $this->_mStudents->ifsc_code = $req['ifscCode'];
            $this->_mStudents->branch_name = $req['branchName'];
            $this->_mStudents->is_transport = $req['isTransport'];
            $this->_mStudents->academic_year =   $fy;
            $this->_mStudents->ip_address = getClientIpAddress();
            $this->_mStudents->admission_no = $studentIdGeneration;
            $this->_mStudents->status = '2';
            $this->_mStudents->user_name =   $username;
            $this->_mStudents->password =   Hash::make('2020');
            $this->_mStudents->user_password =   Hash::make('2020');
            // print_var($this->_mStudents);
            $this->_mStudents->save();

            //insert student transport records
            if ($req['isTransport'] == true) {
                $mStudentTransport = new StudentTransport();
                $mStudentTransport->std_tbl_id = $this->_mStudents->id;
                $mStudentTransport->route_id = $req['routeId'];
                $mStudentTransport->pick_up_point_id = $req['pickUpPointId'];
                $mStudentTransport->academic_year = $fy;
                $mStudentTransport->ip_address = getClientIpAddress();
                $mStudentTransport->save();
            }

            //insert single data and multi data for student sibling 
            if ($req['siblingDetails'] != "") {
                foreach ($req['siblingDetails'] as $key => $ob) {
                    $mStudentSibling = new StudentSibling();
                    $mStudentSibling->std_tbl_id = $this->_mStudents->id;
                    $mStudentSibling->sibling_name = $ob['siblingName'];
                    $mStudentSibling->sibling_admission_no = $ob['siblingAdmissionNo'];
                    $mStudentSibling->sibling_roll_no = $ob['siblingRollNo'];
                    $mStudentSibling->class_id = $ob['siblingClass'];
                    $mStudentSibling->section_id = $ob['siblingSection'];
                    $mStudentSibling->academic_year = $fy;
                    $mStudentSibling->ip_address = getClientIpAddress();
                    $mStudentSibling->save();
                }
            }
            $this->_mSms->smsForAmount($req->amount, $req['Mobile']);
            // $this->sms($req->amount, $req['Mobile']);
            DB::commit();
            // dd();
            $result['basicDetails'] = $this->_mStudents;
            $result['transportDetails'] = $mStudentTransport;
            $result['siblingDetails'] = $mStudentSibling;
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Student Registration Done Successfully", $result, "API_4.01", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.01", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //Login Student
    public function login(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'admissionNo' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            //check email existing or not
            $mStudentMaster = Student::where('admission_no', $req->admissionNo)->first();
            $rolId = $mStudentMaster->role_id;
            if ($mStudentMaster->status == 0) {
                $chkStatus = "Not Active";
            }
            if ($mStudentMaster->status == 1) {
                $chkStatus = "Active";
            }

            $mStudentMasterRole = DB::table('roles')
                ->select(DB::raw("id,role_name"))
                ->where('id', $rolId)
                ->where('status', 1)
                ->first();
            $roleName =    $mStudentMasterRole->role_name;

            if (!$mStudentMaster) {
                $msg = "Oops! Given username does not exist";
                return responseMsg(false, $msg, "");
            }
            // check if user deleted or not
            if ($mStudentMaster->status == 0) {
                $msg = "Cant logged in!! You Have Been Suspended or Deleted !";
                return responseMsg(false, $msg, "");
            }
            // $formatDate = date('Y-m-d', strtotime($mStudentMaster->dob));
            if ($mStudentMaster && Hash::check($req->password, $mStudentMaster->password)) {
                $token = $mStudentMaster->createToken('auth_token')->plainTextToken;
                $mStudentMaster->remember_token = $token;
                $mStudentMaster->save();
                $data1 = [
                    'id' => $mStudentMaster->id,
                    'name' => $mStudentMaster->first_name,
                    'class' => $mStudentMaster->class_name,
                    'section' => $mStudentMaster->section_name,
                    'admissionNo' => $mStudentMaster->admission_no,
                    'mobile' => $mStudentMaster->mobile,
                    'rollNo' => $mStudentMaster->roll_no,
                    'address' => $mStudentMaster->p_address1,
                    'gender' => $mStudentMaster->gender,
                    'fatherName' => $mStudentMaster->fathers_name,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'roleName' => $roleName,
                    'roleId' => $mStudentMaster->role_id,
                    'academicYear' => $mStudentMaster->academic_year,
                    'status' => $chkStatus
                ];
            } else
                throw new Exception("Password is Incorrect");
            if (!$data1)
                throw new Exception("Record Not Found!");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Login Successfully", $data1, "API_4.02", $queryTime, responseTime(), "POST", $req->deviceId ?? "", $token);
        } catch (Exception $e) {
            return responseMsgsT(false, $e->getMessage(), "API_4.02", "", "", "", "post", "", "");
        }
    }

    //Login for parents
    public function loginForParents(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'userName' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $mStudentMaster = Student::where('user_name', $req->userName)
                ->where('status', 1)
                ->first();
            // dd($mStudentMaster);
            // die;
            if ($mStudentMaster->status == 0) {
                $chkStatus = "Not Active";
            }
            if ($mStudentMaster->status == 1) {
                $chkStatus = "Active";
            }

            $rolId = '9'; // for parents
            $mStudentMasterRole = DB::table('roles')
                ->select(DB::raw("id,role_name"))
                ->where('id', $rolId)
                ->where('status', 1)
                ->first();
            $roleName =    $mStudentMasterRole->role_name;

            if (!$mStudentMaster) {
                $msg = "Oops! Given username does not exist";
                return responseMsg(false, $msg, "");
            }
            // check if user deleted or not
            if ($mStudentMaster->status == 0) {
                $msg = "Cant logged in!! You Have Been Suspended or Deleted !";
                return responseMsg(false, $msg, "");
            }
            // $formatDate = date('Y-m-d', strtotime($mStudentMaster->dob));
            if ($mStudentMaster && Hash::check($req->password, $mStudentMaster->user_password)) {
                $token = $mStudentMaster->createToken('auth_token')->plainTextToken;
                $mStudentMaster->remember_token = $token;
                $mStudentMaster->save();
                $data1 = [
                    'id' => $mStudentMaster->id,
                    'name' => $mStudentMaster->first_name,
                    'class' => $mStudentMaster->class_name,
                    'section' => $mStudentMaster->section_name,
                    'admissionNo' => $mStudentMaster->admission_no,
                    'mobile' => $mStudentMaster->mobile,
                    'rollNo' => $mStudentMaster->roll_no,
                    'address' => $mStudentMaster->p_address1,
                    'gender' => $mStudentMaster->gender,
                    'fatherName' => $mStudentMaster->fathers_name,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'roleName' => $roleName,
                    // 'roleId' => $mStudentMaster->role_id,
                    'roleId' => '9',
                    'academicYear' => $mStudentMaster->academic_year,
                    'status' => $chkStatus
                ];
                //$data1 = ['id' => $mStudentMaster->id, 'name' => $mStudentMaster->first_name, 'admissionNo' => $mStudentMaster->admission_no, 'token' => $token, 'token_type' => 'Bearer', 'roleName' => $roleName, 'roleId' => $rolId, 'academicYear' => $mStudentMaster->academic_year];
            } else
                throw new Exception("Password is Incorrect");
            if (!$data1)
                throw new Exception("Record Not Found!");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Login Successfully", $data1, "API_4.04", $queryTime, responseTime(), "POST", $req->deviceId ?? "", $token);
        } catch (Exception $e) {
            return responseMsgsT(false, $e->getMessage(), "API_4.04", "", "", "", "post", "", "");
        }
    }

    //creating user name using first name of the student, first three letters of the fathers first name and last number of admission no
    function generateUsername($fullName, $fatherName, $admissionNo)
    {
        $nameParts = explode(' ', $fullName);
        $fatherNameParts = explode(' ', $fatherName);
        $admissionNo = explode('/', $admissionNo);
        //if (count($nameParts) >= 1 && count($fatherNameParts) >= 1) {
        $firstName = $nameParts[0];
        $lastName = $nameParts[1];
        $fatherFirstName = $fatherNameParts[0];
        $admNo = $admissionNo[2];
        $username = Str::lower($firstName) . '_'; // first name of the student
        $username .= Str::lower(substr($fatherFirstName, 0, 3)); // First three letters of father's name
        $username .= $admNo; // Admission number after Y(year)
        return $username;
        //$username = Str::lower(substr($firstName, 0, 1)); // First letter of first name
        //$username .= Str::lower(substr($lastName, 0, 3)); // First three letters of last name
        // $username .= $admNo; // Admission number         
        // $username .= Str::lower(substr($admNo, 1, 2)); // last two digit of Admission number         
        //}
        //return null; // Handle cases where the full name or father's name doesn't have the required parts
    }

    //commenting bcz sms function added in sms model-------------
    //for sms
    // public function sms($amount, $mobile)
    // {
    //     $curl = curl_init();
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2?authorization=DmryNudVAbzpJ1qkHs5WILw8MX7REStlG2i0KeTc6o39gjhZvPZnMKbIB8aSuj6lqk9hcGVgRQA5rOmC&variables_values=" . $amount  . "&route=otp&numbers=" . urlencode($mobile),
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => "",
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 30,
    //         CURLOPT_SSL_VERIFYHOST => 0,
    //         CURLOPT_SSL_VERIFYPEER => 0,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => "GET",
    //         CURLOPT_HTTPHEADER => array(
    //             "cache-control: no-cache"
    //         ),
    //     ));

    //     $response = curl_exec($curl);
    //     $err = curl_error($curl);
    //     curl_close($curl);
    // }
    //------------------------------------------------------------

    /** ------------------------------------------------ API For Reporting   ---------------------------------------------*/
    //get payment status
    public function getAllOnlineStudent(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'financialYear' => 'required',
            'paymentStatus' => 'required'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $schoolId =  $this->_tCustome->forOnlineRegSchool();
            $std = $this->_mStudents->getAllStudent($req, $schoolId);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Records", $std, "API_4.03", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_4.03", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
