<?php

namespace App\Http\Controllers\API\Examination;

use App\Http\Controllers\Controller;
use App\Models\Examination\MarksTabulation;
use App\Models\Student\student;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class MarksTabulationController extends Controller
{
    //global variable 
    private $_mMarksTabulations;

    public function __construct()
    {
        $this->_mMarksTabulations = new MarksTabulation();
    }

    // Add records
    public function store(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'admissionNo' => 'required',
            'tabulation' => 'required|array',
            'tabulation.*.classId' => 'required|numeric',
            'tabulation.*.termId' => 'required|numeric',
            'tabulation.*.marksEntryId' => 'required|numeric',
            'tabulation.*.obtainedMarks' => 'required|numeric',
            'tabulation.*.fy' => 'required'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {

            $mStudents = Student::where('admission_no', $req->admissionNo)
                ->where('status', 1)
                ->first();
            // print_var($mStudents);
            // die;
            if (collect($mStudents)->isEmpty())
                throw new Exception('Admission no is not existing');
            // dd($mStudents);
            $studentId  = $mStudents->id;

            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));
            $data = array();
            if ($req['tabulation'] != "") {
                foreach ($req['tabulation'] as $ob) {
                    $isGroupExists = $this->_mMarksTabulations->readMarksTabulationGroup($ob, $studentId);
                    // print_var($isGroupExists);
                    // die;
                    if (collect($isGroupExists)->isNotEmpty())
                        throw new Exception("Marks Tabulation Already Existing");

                    $marksTabulation = new MarksTabulation;
                    $marksTabulation->fy_name = $ob['fy'];
                    $marksTabulation->class_id =  $ob['classId'];
                    // $marksTabulation->section_id = $ob['sectionId'];
                    $marksTabulation->student_id = $studentId;
                    $marksTabulation->term_id = $ob['termId'];
                    $marksTabulation->marks_entry_id = $ob['marksEntryId'];
                    $marksTabulation->obtained_marks = $ob['obtainedMarks'];
                    $marksTabulation->academic_year = $fy;
                    $marksTabulation->school_id = authUser()->school_id;
                    $marksTabulation->created_by = authUser()->id;
                    $marksTabulation->ip_address = getClientIpAddress();
                    $marksTabulation->save();
                    // dd($marksTabulation);
                    $data[] = $marksTabulation;
                }
                // die;
            }

            return responseMsgs(true, "Successfully Saved", [$data], "", "API_13.1", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_13.1", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //View All 
    public function retrieveAll(Request $req)
    {
        try {
            $getData = $this->_mMarksTabulations->retrieve();
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
            return responseMsgsT(true, "View All Records", $list, "API_13.2", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_13.2", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
