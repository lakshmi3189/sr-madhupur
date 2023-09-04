<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Admin\UserController;                      //API_1
use App\Http\Controllers\API\Admin\PasswordResetController;             //API_2
use App\Http\Controllers\API\Employee\EmployeeController;               //API_3
use App\Http\Controllers\API\Student\StudentController;                 //API_4
use App\Http\Controllers\API\Admin\SchoolMasterController;              //API_5
use App\Http\Controllers\API\TimeTable\TimeTableController;             //API_6
use App\Http\Controllers\CategoryController;                            //API_7             //Testing Repository
use App\Http\Controllers\API\Event\EventController;                     //API_8
use App\Http\Controllers\API\Examination\ExamTermController;            //API_9
use App\Http\Controllers\API\Holiday\HolidayController;                 //API_10
use App\Http\Controllers\API\Examination\MarksEntryController;          //API_11
use App\Http\Controllers\API\Attendance\StudentAttendanceController;    //API_12
use App\Http\Controllers\API\Examination\MarksTabulationController;     //API_13
use App\Http\Controllers\API\Auth\AuthController;                       //API_14
use App\Http\Controllers\API\FeeStructure\FeeCollectionController;      //API_15
use App\Http\Controllers\API\Payment\PaymentController;                 //API_16
use App\Http\Controllers\API\Payment\PaymentModeController;             //API_17
use App\Http\Controllers\API\EBook\EBookController;                     //API_18
use App\Http\Controllers\API\Employee\EmployeeEducationController;      //API_19
use App\Http\Controllers\API\Employee\EmployeeExperienceController;     //API_20
use App\Http\Controllers\API\Employee\EmployeeFamilyController;         //API_21
use App\Http\Controllers\API\Gallery\GalleryController;                 //API_22
use App\Http\Controllers\API\Report\AttendanceReportController;         //API_23
use App\Http\Controllers\API\Report\PaymentReportController;            //API_24
use App\Http\Controllers\API\Library\BookCategoryController;            //API_25
use App\Http\Controllers\API\Employee\EmployeeRoleMapController;        //API_26
use App\Http\Controllers\API\Attendance\EmployeeAttendanceController;   //API_27



/**
 * | Created On : 01-04-2023 
 * | Author : Lakshmi Kumari
 * | Routes Specified for the Main Module
 * | Code Status : Open 
 */

/*================================================================ Public Routes Start ======================================*/

//Admin 
Route::controller(UserController::class)->group(function () {
    // Route::post('/register', 'register');                                    // User Register                API_1.01
    Route::post('users/login', 'login');                                        // User Login                   API_1.02    
});

//Auth 
Route::controller(AuthController::class)->group(function () {
    Route::post('users/authentication', 'login');                               // Auth Login                   API_14.01    
});

// For Student Online Registration Form 
Route::controller(StudentController::class)->group(function () {
    Route::post('student/online-registration/store', 'onlineRegistration');     // Add Records                  API_4.01  
    Route::post('student/login', 'login');                                      // login                        API_4.02 
    Route::post('student-list', 'getAllOnlineStudent');                         // login                        API_4.03 
    Route::post('parents/login', 'loginForParents');                            // parents login                API_4.04 

});

//For schools  
Route::controller(SchoolMasterController::class)->group(function () {
    Route::post('school-masters/registration', 'registration');                 // School Register              API_5.01
    Route::post('school-masters/login', 'login');                               // School Login                 API_5.02
    Route::post('school-masters/search-username', 'searchUserName');            // User Name Existing           API_5.03
});

//Send Mail
Route::controller(PasswordResetController::class)->group(function () {
    Route::post('/sendResetPasswordEmail', 'sendResetPasswordEmail');           // Send Reset Password For User API_2.01
    Route::post('/resetPassword/{token}', 'resetPassword');                     // Reset Password  For User     API_2.02
});

Route::controller(EventController::class)->group(function () {
    Route::post('event/crud/active-all', 'activeAll');                            // Active All Records         API_8.01
});

Route::controller(AttendanceReportController::class)->group(function () {
    Route::post('attendance-report/retrieve-all', 'retrieveAll');                                               //API_23.01
});

Route::controller(PaymentReportController::class)->group(function () {
    Route::post('payment-report/retrieve-all', 'retrieveAll');                                                  //API_24.01
});

Route::controller(GalleryController::class)->group(function () {
    Route::post('gallery/crud/retrieve-all', 'retrieveAll');                  // Get all records                 API_22.01
    Route::post('gallery/crud/show-by-name', 'showByName');                   // Get by Id                       API_22.02
    Route::post('gallery/crud/show-image', 'getAllImg');                      // Get all img                     API_22.03
});

/*================================================================ Public Routes End  ======================================*/

/*================================================================ Protected Routes Start ===================================*/
Route::middleware('auth:sanctum')->group(function () {
    //For aadrika
    Route::controller(AuthController::class)->group(function () {
        Route::post('users/change-password', 'changePassword');                 // Auth change password         API_14.1    
        Route::post('users/logout', 'logout');                                  // Auth Logout                  API_14.2   
        Route::post('users/view-profile', 'show');                              // Auth Profile                 API_14.3   
    });

    //For Schools 
    Route::controller(SchoolMasterController::class)->group(function () {
        Route::post('school-masters/view-profile', 'show');                     // User Name Existing           API_5.1
        Route::post('school-masters/change-password', 'changePassword');        // School Register              API_5.2
        Route::post('school-masters/update-profile', 'edit');                   // School Update Profile        API_5.3
        Route::post('school-masters/logout', 'logout');                         // School Logout                API_5.4
        Route::post('school-masters/retrieve-all', 'retrieveAll');              // School retrieve              API_5.5
        Route::post('school-masters/active-all', 'activeAll');                  // School active                API_5.6
        Route::post('school-masters/delete', 'delete');                         // delete                       API_5.7
        Route::post('school-masters/update-role', 'updateRole');                // update role                  API_5.8
    });
});

Route::middleware('auth:sanctum')->group(function () {

    Route::controller(UserController::class)->group(function () {
        Route::get('/profile', 'profile');                                      // View Profile For User        API_1.1
        Route::post('/editProfile', 'editProfile');                             // Edit Profile For User        API_1.2
        Route::post('/deleteProfile', 'deleteProfile');                         // Delete Profile For User      API_1.3
        Route::post('/changePassword', 'changePassword');                       // Change Password For User     API_1.4 
        Route::post('/logout', 'logout');                                       // Logout                       API_1.5
    });

    Route::controller(EmployeeController::class)->group(function () {
        Route::post('employee/crud/store', 'store');                            // Add                          API_3.1
        Route::post('employee/crud/search', 'search');                          // Search                       API_3.2
        Route::post('employee/crud/show', 'show');                              // Get by Id                    API_3.3
        Route::post('employee/crud/edit2', 'edit');                             // Edit                         API_3.4
        Route::post('employee/crud/retrieve-all', 'retrieveAll');               // Fetch all Records            API_3.5
        Route::post('employee/crud/delete', 'delete');                          // Deactive Record              API_3.6
        Route::post('employee/storeCSV', 'storeCSV');                           // Upload CSV                   API_3.7
        Route::post('employee/check-aadhar', 'getDuplicateAadhar');             // check aadhar                 API_3.8
        Route::post('employee/role', 'updateRole');                             // add role                     API_3.9 
        Route::post('employee/faculty-list', 'retrieveAllFaculty');             // Fetch all Records            API_3.10
        Route::post('employee/crud/edit', 'edit');                              // edit                         API_3.11
        Route::post('employee/crud/edit-address', 'editAddress');               // edit address                 API_3.12
        Route::post('employee/crud/edit-bank', 'editBank');                     // edit bank                    API_3.13
        Route::post('employee/attendance', 'store');                            // attendance                   API_3.14

        // Route::post('employee/crud/edit-education', 'editEducation');           // edit education               API_3.14
        // Route::post('employee/crud/edit-experience', 'editExperience');         // edit exp                     API_3.15
        // Route::post('employee/crud/edit-family', 'editFamily');                 // edit family                  API_3.16
    });

    Route::controller(EmployeeEducationController::class)->group(function () {
        Route::post('emp-education/crud/store', 'store');                                                       //API_19.1
        Route::post('emp-education/crud/edit', 'edit');                                                         //API_19.2
        Route::post('emp-education/crud/show', 'show');                                                         //API_19.3
        Route::post('emp-education/crud/retrieve-all', 'retrieveAll');                                          //API_19.4
        Route::post('emp-education/crud/delete', 'delete');                                                     //API_19.5
    });

    Route::controller(EmployeeExperienceController::class)->group(function () {
        Route::post('emp-experience/crud/store', 'store');                                                      //API_20.1
        Route::post('emp-experience/crud/edit', 'edit');                                                        //API_20.2
        Route::post('emp-experience/crud/show', 'show');                                                        //API_20.3
        Route::post('emp-experience/crud/retrieve-all', 'retrieveAll');                                         //API_20.4
        Route::post('emp-experience/crud/delete', 'delete');                                                    //API_20.5
    });
    Route::controller(EmployeeFamilyController::class)->group(function () {
        Route::post('emp-family/crud/store', 'store');                                                          //API_21.1
        Route::post('emp-family/crud/edit', 'edit');                                                            //API_21.2
        Route::post('emp-family/crud/show', 'show');                                                            //API_21.3
        Route::post('emp-family/crud/retrieve-all', 'retrieveAll');                                             //API_21.4
        Route::post('emp-family/crud/delete', 'delete');                                                        //API_21.5
    });

    Route::controller(StudentController::class)->group(function () {
        Route::post('student/crud/store', 'store');                             // Add                          API_4.1
        Route::post('student/crud/edit', 'edit');                               // Edit                         API_4.2
        Route::post('student/student-profile', 'showStudentProfile');           // Get by Id                    API_4.3
        Route::post('student/crud/search', 'search');                           // Serach                       API_4.4
        Route::post('student/crud/retrieve-all', 'retrieveAll');                // Fetch all Records            API_4.5
        Route::post('student/crud/delete', 'delete');                           // Deactive Record              API_4.6
        Route::post('student/crud/active-all', 'activeAll');                    // Active All Records           API_4.7
        Route::post('student/search', 'searchStdByAdmNo');                      // Search By Adm No - For BLL   API_4.8
        Route::post('student/storeCSV', 'storeCSV');                            // Store CSV Data               API_4.9
        Route::post('student/section', 'showStudentGroup');                     // Show student                 API_4.10 
        Route::post('student/id-card', 'getIdCard');                            // Show ID Card                 API_4.11
        Route::post('student/role', 'updateRole');                              // Update Role                  API_4.12
        Route::post('student/count-active', 'countActiveStudent');              // count active student         API_4.13        
        Route::post('parents/change-password', 'parentsChangePassword');        // parents chng pwd             API_4.14
        Route::post('student/logout', 'logout');                                // logout                       API_4.15
        Route::post('student/change-password', 'studentChangePassword');        // parents chng pwd             API_4.16
        Route::post('student/parent-profile', 'showParentsProfile');            // show parents profile         API_4.17
        Route::post('student/class-mates', 'showClassMates');                   // show class mates             API_4.18

    });

    Route::controller(TimeTableController::class)->group(function () {
        Route::post('time-table/crud/store', 'store');                          // Store                        API_6.1
        Route::post('time-table/crud/edit', 'edit');                            // Edit                         API_6.2
        Route::post('time-table/crud/show', 'show');                            // Get by Id                    API_6.3
        Route::post('time-table/crud/retrieve-all', 'retrieveAll');             // Fetch all Records            API_6.4
        Route::post('time-table/crud/delete', 'delete');                        // delete                       API_6.5
        Route::post('time-table/crud/active-all', 'activeAll');                 // Active All Records           API_6.6
        Route::post('time-table/crud/search', 'search');                        // Search                       API_6.7
        Route::post('time-table/view-list', 'getTimeTable');                    // get list                     API_6.8
        Route::post('time-table/view-faculty', 'getFacultyList');               // get faculty list             API_6.9

    });

    Route::controller(EventController::class)->group(function () {
        Route::post('event/crud/store', 'store');                               // Store                        API_8.1
        Route::post('event/crud/edit', 'edit');                                 // Edit                         API_8.2
        Route::post('event/crud/show', 'show');                                 // Get by Id                    API_8.3
        Route::post('event/crud/retrieve-all', 'retrieveAll');                  // Get all records              API_8.4
        Route::post('event/crud/delete', 'delete');                             // delete                       API_8.5
        Route::post('event/crud/active-all', 'activeAll');                      // Active All Records           API_8.6
        Route::post('event/crud/search', 'search');                             // Search                       API_8.7
    });

    Route::controller(ExamTermController::class)->group(function () {
        Route::post('exam-term/crud/store', 'store');                           // Store                        API_9.1
        Route::post('exam-term/crud/edit', 'edit');                             // Edit                         API_9.2
        Route::post('exam-term/crud/show', 'show');                             // Get by Id                    API_9.3
        Route::post('exam-term/crud/retrieve-all', 'retrieveAll');              // Get all records              API_9.4
        Route::post('exam-term/crud/delete', 'delete');                         // delete                       API_9.5
        Route::post('exam-term/crud/active-all', 'activeAll');                  // Active All Records           API_9.6
        Route::post('exam-term/crud/search', 'search');                         // Search                       API_9.7
    });

    Route::controller(HolidayController::class)->group(function () {
        Route::post('holiday/crud/store', 'store');                             // Store                        API_10.1
        Route::post('holiday/crud/edit', 'edit');                               // Edit                         API_10.2
        Route::post('holiday/crud/show', 'show');                               // Get by Id                    API_10.3
        Route::post('holiday/crud/retrieve-all', 'retrieveAll');                // Get all records              API_10.4
        Route::post('holiday/crud/delete', 'delete');                           // delete                       API_10.5
        Route::post('holiday/crud/active-all', 'activeAll');                    // Active All Records           API_10.6
        Route::post('holiday/crud/search', 'search');                           // Search                       API_10.7
        Route::post('holiday/storeCSV', 'storeCSV');                            // Store CSV Data               API_10.8
    });

    Route::controller(MarksEntryController::class)->group(function () {
        Route::post('marks-entry/crud/store', 'store');                         // Store                        API_11.1
        Route::post('marks-entry/crud/edit', 'edit');                           // Update                       API_11.2
        Route::post('marks-entry/crud/show', 'show');                           // Get by Id                    API_11.3
        Route::post('marks-entry/crud/retrieve-all', 'retrieveAll');            // Get all records              API_11.4
        Route::post('marks-entry/crud/delete', 'delete');                       // delete                       API_11.5
        Route::post('marks-entry/crud/active-all', 'activeAll');                // Get active all               API_11.6
        Route::post('marks-entry/crud/search', 'search');                       // Search                       API_11.7
        Route::post('marks-entry/section', 'sectionWiseMarks');                 // Get active all               API_11.8
    });

    Route::controller(StudentAttendanceController::class)->group(function () {
        Route::post('student-attendance/crud/store', 'store');                  // Store                        API-12.1        
        Route::post('student-attendance/viewAttendance', 'viewAttendanceList'); // search                       API-12.2        
    });

    Route::controller(MarksTabulationController::class)->group(function () {
        Route::post('marks-tabulation/crud/store', 'store');                    // Store                        API_13.1
        Route::post('marks-tabulation/crud/retrieve-all', 'retrieveAll');       // Get all records              API_13.2
    });

    Route::controller(FeeCollectionController::class)->group(function () {
        Route::post('fee-collection/crud/store', 'store');                      // Store                        API_15.1
        Route::post('fee-collection/fees', 'searchFeesByAdmNo');                // Search bt adm no             API_15.2
        Route::post('fee-collection/receipt', 'showReceipt');                   // show receipt                 API_15.3
        Route::post('fee-collection/view-receipt', 'showByReceiptNo');          // show receipt                 API_15.4
        Route::post('parents/receipt-list', 'showAllReceiptList');              // list of receipt              API_15.5

    });

    Route::controller(PaymentController::class)->group(function () {
        Route::post('payment/crud/store', 'store');                             // Store                        API_16.1
        Route::post('payment/crud/edit', 'edit');                               // Edit                         API_16.2
        Route::post('payment/crud/show', 'show');                               // Get by Id                    API_16.3
        Route::post('payment/crud/retrieve-all', 'retrieveAll');                // Get all records              API_16.4
        Route::post('payment/crud/delete', 'delete');                           // delete                       API_16.5
        Route::post('payment/crud/active-all', 'activeAll');                    // Active All Records           API_16.6
        Route::post('payment/crud/search', 'search');                           // Search                       API_16.7
    });

    Route::controller(PaymentModeController::class)->group(function () {
        Route::post('payment-mode/crud/store', 'store');                        // Store                        API_17.1
        Route::post('payment-mode/crud/edit', 'edit');                          // Edit                         API_17.2
        Route::post('payment-mode/crud/show', 'show');                          // Get by Id                    API_17.3
        Route::post('payment-mode/crud/retrieve-all', 'retrieveAll');           // Get all records              API_17.4
        Route::post('payment-mode/crud/delete', 'delete');                      // delete                       API_17.5
        Route::post('payment-mode/crud/active-all', 'activeAll');               // Active All Records           API_17.6
        Route::post('payment-mode/crud/search', 'search');                      // Search                       API_17.7
    });

    Route::controller(EBookController::class)->group(function () {
        Route::post('ebook/crud/store', 'store');                               // Store                        API_18.1
        Route::post('ebook/crud/edit', 'edit');                                 // Edit                         API_18.2
        Route::post('ebook/crud/show', 'show');                                 // Get by Id                    API_18.3
        Route::post('ebook/crud/retrieve-all', 'retrieveAll');                  // Get all records              API_18.4
        Route::post('ebook/crud/delete', 'delete');                             // delete                       API_18.5
        Route::post('ebook/crud/active-all', 'activeAll');                      // Active All Records           API_18.6
        Route::post('ebook/crud/search', 'search');                             // Search                       API_18.7
        Route::post('ebook/view-book', 'showClassWiseBook');                    // book list                    API_18.8
    });

    Route::controller(GalleryController::class)->group(function () {
        Route::post('gallery/crud/store', 'store');                             // Store                        API_22.1
        Route::post('gallery/crud/edit', 'edit');                               // Edit                         API_22.2
        Route::post('gallery/crud/show', 'show');                               // Get by Id                    API_22.3
        Route::post('gallery/crud/retrieve-all', 'retrieveAll');                // Get all records              API_22.4
        Route::post('gallery/crud/delete', 'delete');                           // delete                       API_22.5
        Route::post('gallery/crud/active-all', 'activeAll');                    // Active All Records           API_22.6 
        Route::post('gallery/crud/search', 'search');                           // Search                       API_22.7
        Route::post('gallery/crud/show-by-name', 'showByName');                 // show by name                 API_22.8
    });

    Route::controller(BookCategoryController::class)->group(function () {
        Route::post('book-category/crud/store', 'store');                       // Store                        API_25.1
        Route::post('book-category/crud/edit', 'edit');                         // Edit                         API_25.2
        Route::post('book-category/crud/show', 'show');                         // Get by Id                    API_25.3
        Route::post('book-category/crud/retrieve-all', 'retrieveAll');          // Get all records              API_25.4
        Route::post('book-category/crud/delete', 'delete');                     // delete                       API_25.5
        Route::post('book-category/crud/active-all', 'activeAll');              // Active All Records           API_25.6 
        Route::post('book-category/crud/search', 'search');                     // Search                       API_25.7
    });

    Route::controller(EmployeeRoleMapController::class)->group(function () {
        Route::post('employee-role/store', 'store');                            // Store                        API_26.1
        Route::post('employee-role/edit', 'edit');                              // Edit                         API_26.2
        Route::post('employee-role/show', 'show');                              // Get by Id                    API_26.3
        Route::post('employee-role/retrieve-all', 'retrieveAll');               // Get all records              API_26.4
        Route::post('employee-role/delete', 'delete');                          // delete                       API_26.5
        Route::post('employee-role/active-all', 'activeAll');                   // Active All Records           API_26.6 
        Route::post('employee-role/search', 'search');                          // Search                       API_26.7
    });

    Route::controller(EmployeeAttendanceController::class)->group(function () {
        Route::post('employee-attendance/store', 'store');                       // attendance                   API_27.1
        Route::post('employee-attendance/edit', 'edit');                         // Edit                         API_27.2
        Route::post('employee-attendance/show', 'show');                         // Get by Id                    API_27.3
        Route::post('employee-attendance/retrieve-all', 'retrieveAll');          // Get all records              API_27.4
        Route::post('employee-attendance/delete', 'delete');                     // delete                       API_27.5
        Route::post('employee-attendance/active-all', 'activeAll');              // Active All Records           API_27.6 
        Route::post('employee-attendance/search', 'search');                     // Search                       API_27.7
        // Route::post('employee/viewAttendance', 'viewAttendanceList');         // search                       API_27.2        

    });
});
/*================================================================ Protected Routes End ===================================*/


//Get Document
Route::get('/getImageLink', function () {
    return view("getImageLink");                                                // Get Document
});

//Routes for repository - demo
Route::get('/category', [CategoryController::class, 'index']);                  // Read                         API_7.1
Route::post('/category', [CategoryController::class, 'store']);                 // Store                        API_7.2
