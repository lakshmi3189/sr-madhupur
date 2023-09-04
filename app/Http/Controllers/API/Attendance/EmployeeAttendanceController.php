<?php

namespace App\Http\Controllers\API\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\EmployeeAttendance;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Employee\Employee;
use Illuminate\Support\Facades\Validator;
use DB;

class EmployeeAttendanceController extends Controller
{
    //global variable
    private $_mEmployeeAttendances;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mEmployeeAttendances = new EmployeeAttendance();
    }

    // Add records 
    //note: 1-present; 2-absent; 3-on leave
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'attendance' => 'required|array',
            'attendance.*.empNo' => 'string',
            'attendance.*.empId' => 'numeric',
            'attendance.*.attendanceDate' => 'required|date',
            'attendance.*.attendanceStatus' => 'numeric',
            'attendance.*.description' => 'string|nullable'

        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            // print_var($req['attendance']);
            // die;
            $result = array();
            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));
            // $mEmployees = Employee::where('emp_no', $req['empNo'])
            //     ->where('status', 1)
            //     ->first();
            // echo  $empId  = $mEmployees->id;
            // die;
            if ($req['attendance'] != "") {
                foreach ($req['attendance'] as $ob) {
                    $isGroupExists = $this->_mEmployeeAttendances->readEmployeeAttendanceGroup($ob);
                    if (collect($isGroupExists)->isNotEmpty())
                        throw new Exception("Attendance of " . $ob['empNo'] . " Already Existing");
                    $empAttendance = new EmployeeAttendance;
                    $empAttendance->emp_id = $ob['empId'];
                    $empAttendance->attendance_status = $ob['attendanceStatus'];
                    $empAttendance->description = $ob['description'];
                    $empAttendance->attendance_date = $ob['attendanceDate'];
                    $empAttendance->academic_year = $fy;
                    $empAttendance->school_id = authUser()->school_id;
                    $empAttendance->created_by = authUser()->id;
                    $empAttendance->ip_address = getClientIpAddress();
                    // print_var($empAttendance);
                    // die;
                    $empAttendance->save();
                }
            }
            $result['attendance'] = $empAttendance;
            return responseMsgs(true, "Successfully Saved", [], "", " API_3.14", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], " API_3.14", "", responseTime(), "POST", $req->deviceId ?? "");
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
            $getData = $this->_mEmployeeAttendances->getEmployeeAttendance($req);
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
            // $Routes = $this->_mEmployeeAttendances::orderByDesc('id')->where('status', '1')->get();
            $getAll = $this->_mEmployeeAttendances->retrieveAll();
            return responseMsgs(true, "", $getAll, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
