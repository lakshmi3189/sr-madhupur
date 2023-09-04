<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class DiscountGroup extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function store(array $req)
    {
        DiscountGroup::create($req);
    }

    /*Read Records by name*/
    public function readGroupByDiscountGroup($req)
    {
        $schoolId = authUser()->school_id;
        // $createdBy = authUser()->id;
        return DiscountGroup::where(DB::raw('upper(discount_group)'), strtoupper($req->discountGroup))
            ->where('status', 1)
            // ->where('school_id', $schoolId)
            // ->where('created_by', $createdBy)
            ->get();
    }

    /*Read Records by ID*/
    public function getGroupById($id)
    {
        $schoolId = authUser()->school_id;
        // $createdBy = authUser()->id;
        return DiscountGroup::select(
            DB::raw("id,discount_group,description,
              CASE 
              WHEN status = '0' THEN 'Deactivated'  
              WHEN status = '1' THEN 'Active'
              END as status,
              TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
              TO_CHAR(created_at,'HH12:MI:SS AM') as time
              ")
        )
            ->where('id', $id)
            // ->where('school_id', $schoolId)
            // ->where('created_by', $createdBy)
            // ->where('status', 1)
            ->first();
    }

    /*Read all Records by*/
    public function retrieve()
    {
        $schoolId = authUser()->school_id;
        // $createdBy = authUser()->id;
        return DiscountGroup::select(
            DB::raw("id,discount_group,description,
            CASE 
            WHEN status = '0' THEN 'Deactivated'  
            WHEN status = '1' THEN 'Active'
            END as status,
            TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
            TO_CHAR(created_at,'HH12:MI:SS AM') as time
            ")
        )
            // ->where('school_id', $schoolId)
            // ->where('created_by', $createdBy)
            ->orderByDesc('id');
        // ->get();
    }

    //Get Records by name
    public function searchByName($req)
    {
        $schoolId = authUser()->school_id;
        // $createdBy = authUser()->id;
        return DiscountGroup::select(
            DB::raw("id,discount_group,description,
            CASE 
            WHEN status = '0' THEN 'Deactivated'  
            WHEN status = '1' THEN 'Active'
            END as status,
            TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
            TO_CHAR(created_at,'HH12:MI:SS AM') as time
            ")
        )
            ->where(DB::raw('upper(discount_group)'), 'LIKE', '%' . strtoupper($req->search) . '%');
            // ->where('school_id', $schoolId);
        // ->where('created_by', $createdBy);
        // ->get();
    }

    /*Read all Active Records*/
    public function active()
    {
        $schoolId = authUser()->school_id;
        // $createdBy = authUser()->id;
        return DiscountGroup::select(
            DB::raw("id,discount_group,description,
            CASE 
            WHEN status = '0' THEN 'Deactivated'  
            WHEN status = '1' THEN 'Active'
            END as status,
            TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
            TO_CHAR(created_at,'HH12:MI:SS AM') as time
            ")
        )
            ->where('status', 1)
            // ->where('school_id', $schoolId)
            // ->where('created_by', $createdBy)
            ->orderBy('discount_group')
            ->get();
    }
}
