<?php

namespace App\Http\Controllers\API\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\StudentAttendance;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use DB;

class StudentAttendanceController extends Controller
{
    //global variable
    private $_mStudentAttendances;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mStudentAttendances = new StudentAttendance();
    }

    // Add records 
    //note: 1-present; 2-absent; 3-on leave
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'attendance' => 'required|array',
            'attendance.*.classId' => 'required|numeric',
            'attendance.*.admissionNo' => 'string',
            'attendance.*.attendanceDate' => 'required|date',
            'attendance.*.attendanceStatus' => 'numeric',
            'attendance.*.description' => 'string|nullable'

        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $result = array();
            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));
            if ($req['attendance'] != "") {
                foreach ($req['attendance'] as $ob) {
                    $isGroupExists = $this->_mStudentAttendances->readStudentAttendanceGroup($ob);
                    if (collect($isGroupExists)->isNotEmpty())
                        throw new Exception("Attendance of " . $ob['admissionNo'] . " Already Existing");
                    $stdAttendance = new StudentAttendance;
                    $stdAttendance->class_id = $ob['classId'];
                    $stdAttendance->section_id = $ob['sectionId'];
                    $stdAttendance->student_id = $ob['studentId'];
                    $stdAttendance->attendance_status = $ob['attendanceStatus'];
                    $stdAttendance->description = $ob['description'];
                    $stdAttendance->attendance_date = $ob['attendanceDate'];
                    $stdAttendance->academic_year = $fy;
                    $stdAttendance->school_id = authUser()->school_id;
                    $stdAttendance->created_by = authUser()->id;
                    $stdAttendance->ip_address = getClientIpAddress();
                    // print_var($stdAttendance);
                    // die;
                    $stdAttendance->save();
                }
            }
            $result['attendance'] = $stdAttendance;
            return responseMsgs(true, "Successfully Saved", [], "", " API-12.1", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], " API-12.1", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //Search By ClassId & SectionId
    public function viewAttendanceList(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'search' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $getData = $this->_mStudentAttendances->getStudentAttendance($req);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Records", $getData, "API-12.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API-12.1", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //View All
    public function retrieveAll(Request $req)
    {
        try {
            // $Routes = $this->_mStudentAttendances::orderByDesc('id')->where('status', '1')->get();
            $getAll = $this->_mStudentAttendances->retrieveAll();
            return responseMsgs(true, "", $getAll, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
