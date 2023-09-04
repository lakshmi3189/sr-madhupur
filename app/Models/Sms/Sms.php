<?php

namespace App\Models\Sms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/*
Created By : Lakshmi kumari 
Created On : 23-Aug-2023 
Code Status : Open 
*/

class Sms extends Model
{
  use HasFactory;
  //use HasFactory;
  protected $guarded = [];

  //sms for online reg form and online payment
  public function smsForAmount($amount, $mobile)
  {
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2?authorization=DmryNudVAbzpJ1qkHs5WILw8MX7REStlG2i0KeTc6o39gjhZvPZnMKbIB8aSuj6lqk9hcGVgRQA5rOmC&variables_values=" . $amount  . "&route=otp&numbers=" . urlencode($mobile),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache"
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
  }

  public function smsForFee($admissionNo, $total)
  {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2?authorization=DmryNudVAbzpJ1qkHs5WILw8MX7REStlG2i0KeTc6o39gjhZvPZnMKbIB8aSuj6lqk9hcGVgRQA5rOmC&variables_values=" . $total . "&route=otp&numbers=" . urlencode('8252586867'),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache"
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    // if ($err) {
    //     echo "cURL Error #:" . $err;
    // } else {
    //     echo $response;
    // }
  }
}
