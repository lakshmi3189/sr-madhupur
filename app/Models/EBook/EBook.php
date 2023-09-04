<?php

namespace App\Models\EBook;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EBook extends Model
{
    use HasFactory;
    protected $guarded = [];
    // protected $table = 'e_books';

    /*Add Records*/
    public function store(array $req)
    {
        // echo 'ok';
        // die;
        EBook::create($req);
    }

    /*Read Records by name*/
    public function readEBookGroup($req)
    {
        $schoolId = authUser()->school_id;
        return EBook::where(DB::raw('upper(book_name)'), strtoupper($req->bookName))
            ->where('author_name', $req->authorName)
            ->where('publish_by', $req->publishBy)
            ->where('status', 1)
            // ->where('school_id', $schoolId)
            ->get();
    }

    //Get Records by name
    public function searchByName($req)
    {
        //version1
        //     $schoolId = authUser()->school_id;
        //     return EBook::select(
        //         DB::raw("id,book_name,author_name,publish_by,published_date,price,ebook_docs,cover_pic_docs,
        //     CASE 
        //     WHEN status = '0' THEN 'Deactivated'  
        //     WHEN status = '1' THEN 'Active'
        //     END as status,
        //     TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        //     TO_CHAR(created_at,'HH12:MI:SS AM') as time
        //   ")
        //     )
        //         ->where('book_name', 'ilike', $req->search . '%');
        //     // ->where('school_id', $schoolId);
        //     // ->where('status', 1)
        //     // ->get();

        //version2
        return DB::table('e_books as a')
            ->select(
                DB::raw("
                a.id,a.book_name,a.author_name,a.publish_by,a.published_date,a.price,a.ebook_docs,a.cover_pic_docs,
                b.class_name,
                CASE 
                WHEN a.status = '0' THEN 'Deactivated'  
                WHEN a.status = '1' THEN 'Active'
                END as status,
                TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
                TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
                ")
            )
            ->join('class_masters as b', 'b.id', '=', 'a.class_id')
            ->where('a.book_name', 'ilike', $req->search . '%');
    }

    /*Read Records by ID*/
    public function getGroupById($id)
    {
        $schoolId = authUser()->school_id;
        //version1
        // return EBook::select(
        //     DB::raw("
        //     id,book_name,author_name,publish_by,published_date,price,ebook_docs,cover_pic_docs,
        //     CASE 
        //     WHEN status = '0' THEN 'Deactivated'  
        //     WHEN status = '1' THEN 'Active'
        //     END as status,
        //     TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        //     TO_CHAR(created_at,'HH12:MI:SS AM') as time
        // ")
        // )

        //version2
        return DB::table('e_books as a')
            ->select(
                DB::raw("
            a.id,a.book_name,a.author_name,a.publish_by,a.published_date,a.price,a.ebook_docs,a.cover_pic_docs,
            b.class_name,
            CASE 
            WHEN a.status = '0' THEN 'Deactivated'  
            WHEN a.status = '1' THEN 'Active'
            END as status,
            TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
            TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
            ")
            )
            ->join('class_masters as b', 'b.id', '=', 'a.class_id')
            ->where('a.id', $id)
            ->where('a.status', 1)
            ->first();
    }

    /*Read all Records by*/
    public function retrieve()
    {
        //version1
        //     $schoolId = authUser()->school_id;
        //     return EBook::select(
        //         DB::raw("id,book_name,author_name,publish_by,published_date,price,ebook_docs,cover_pic_docs,
        //     CASE 
        //     WHEN status = '0' THEN 'Deactivated'  
        //     WHEN status = '1' THEN 'Active'
        //     END as status,
        //     TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        //     TO_CHAR(created_at,'HH12:MI:SS AM') as time
        //   ")
        //     )
        //         // ->where('school_id', $schoolId)
        //         ->orderBy('book_name');
        //     // ->get();

        //version 2
        return DB::table('e_books as a')
            ->select(
                DB::raw("
        a.id,a.book_name,a.author_name,a.publish_by,a.published_date,a.price,a.ebook_docs,a.cover_pic_docs,
        b.class_name,
        CASE 
        WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
            )
            ->join('class_masters as b', 'b.id', '=', 'a.class_id')
            ->orderBy('a.book_name');
    }





    /*Read all Active Records*/
    public function active()
    {
        //     // $schoolId = authUser()->school_id;
        //     $viewAll = EBook::select(
        //         DB::raw("id,book_name,author_name,publish_by,price,
        //     CASE 
        //     WHEN status = '0' THEN 'Deactivated'  
        //     WHEN status = '1' THEN 'Active'
        //     END as status,
        //     TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        //     TO_CHAR(created_at,'HH12:MI:SS AM') as time
        //   ")
        //     )
        //         ->where('status', 1)
        // ->where('school_id', $schoolId)
        // ->orderBy('book_name')
        // ->get();

        $viewAll = DB::table('e_books as a')
            ->select(
                DB::raw("
                a.id,a.book_name,a.author_name,a.publish_by,a.published_date,a.price,a.ebook_docs,a.cover_pic_docs,
                b.class_name,
                CASE 
                WHEN a.status = '0' THEN 'Deactivated'  
                WHEN a.status = '1' THEN 'Active'
                END as status,
                TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
                TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
                ")
            )
            ->join('class_masters as b', 'b.id', '=', 'a.class_id')
            // ->where('school_id', $schoolId)
            ->orderBy('a.book_name')
            ->get();

        $data = array();
        foreach ($viewAll as $v) {
            $dataArr = array();
            $path = 'api\getImageLink?path=';
            $file_name = $path . $v->ebook_docs;
            $dataArr['id'] = $v->id;
            $dataArr['ebook_docs'] = $file_name;
            $dataArr['book_name'] = $v->book_name;
            $dataArr['author_name'] = $v->author_name;
            $dataArr['publish_by'] = $v->publish_by;
            $dataArr['published_date'] = $v->published_date;
            $dataArr['class_name'] = $v->class_name;
            $dataArr['price'] = $v->price;
            $dataArr['status'] = $v->status;
            $dataArr['date'] = $v->date;
            $dataArr['time'] = $v->time;
            $data[] = $dataArr;
        }
        return $data;
    }

    public function getClassWiseBook($classId)
    {
        return DB::table('e_books as a')
            ->select(
                DB::raw("
            a.id,a.book_name,a.author_name,a.publish_by,a.published_date,a.price,a.ebook_docs,a.cover_pic_docs,
            b.class_name,
            CASE 
            WHEN a.status = '0' THEN 'Deactivated'  
            WHEN a.status = '1' THEN 'Active'
            END as status,
            TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
            TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
            ")
            )
            ->join('class_masters as b', 'b.id', '=', 'a.class_id')
            ->where('a.class_id', $classId)
            ->where('a.status', 1)
            ->get();
    }
}
