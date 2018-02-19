<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class IndexController extends Controller
{
    /**
     *Setting up connection to client aplication
     *@param $id - value of order id, by defolt is null, if has value , make request to get data from client
     *              side one order, if null get all orders
     *@return array from client application
     */

    public function curl($id=null){

                $curl = curl_init();

                $orderId= isset($id) ? "/".$id : null;
                curl_setopt_array($curl, array(

                    CURLOPT_URL => "https://dummy-api.selesti.agency/orders".$orderId,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                        "Content-Type: application/json",
                        "X-AUTHORISATION: R4o6rHAvpEhUOmVR"
                    ),
                ));


                $response = curl_exec($curl);
                curl_close($curl);
                return json_decode($response,true);

    }


    /**
     * Exporting received array in CSV file
     * @param $rs - array with data
     * @param $header- Column names of created CSV table
     * @param $fname- name of exported file
     * @return mixed
     */
    public function export($rs, $header ,$fname)
    {


        $headers = array(
            'Content-Type'        => 'application/vnd.ms-excel; charset=utf-8',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Disposition' => 'attachment; filename=abc.csv',
            'Expires'             => '0',
            'Pragma'              => 'public',
             );

                $filename = $fname.date("Y-m-d H_i_s") . ".csv";
                $handle = fopen($filename, 'w');
                fputcsv($handle, $header);

                    foreach ($rs as $row) {

                        fputcsv($handle, $row);

                    }

                fclose($handle);


            return Response::download($filename, $filename , $headers);
    }

    /*
     * Addapting received array, with all orders, from curl() method , to fill the data in CSV file
     * @return array
     *
     */

    public function orders()
    {

            $rs=$this->curl();

            if ($rs['error']) {
                echo $rs['error_code'];
            } else {

                $data=$rs['data'];


                $res='';

                for ($i=0; $i<count($data); $i++){

                    $res[$i]['id']=$data[$i]['id'];
                    $res[$i]['isPaidFor']=$data[$i]['isPaidFor']==1? 'yes' : 'no';
                    $res[$i]['name']=$data[$i]['customer']['name'];
                    $res[$i]['email']=$data[$i]['customer']['email'];
                }

                return $res;
            }




    }


    /*
     * Addapting received array of order selected by id, from curl() method , to fill the data in CSV file
     * @return array
     *
     */

    public function order($id){


            $rs=$this->curl($id);

            if ($rs['error']) {
                echo $rs['error_code'];
            } else {


                    $data=$rs['data'];
                    $res='';
                    $res['id']=$data['id'];
                    $res['isPaidFor']=$data['isPaidFor']==true? 'yes' : 'no';
                    $res['name']=$data['customer']['name'];
                    $res['email']=$data['customer']['email'];
                    $res['purchasedItems']=count($data['items']);

                    $prices='';
                    foreach ($data['items'] as $items ){

                        $prices[]= $items['price_in_pennies'];

                    }

               $res['totalPrice']= array_sum($prices);


                return $res;

        }




    }

    /*
     * Displaying list of orders
     *
     */
    public function index()
    {
        $orders=$this->orders();

        return view( 'index',compact('orders'));

    }
    /*
     * Exporting data of order selected by id , to CSV file
     * @param $id - id of order
     * @return .csv file with  data of  order requested by id
     */
    public function CSVOrder($id){

        $rs[]=$this->order($id);
        $headers=array('OrderID','HasCustomerPaid','CustomerName','CustomerEmail','NumberOfItemsPurchased','TotalOrderPriceInPounds');
        return $this->export($rs, $headers,'Order_Nr_'.$id.'_');

    }
    /*
     * Exporting data of all orders to CSV file
     * @return .csv file with  all data
     */
    public function CSVOrders(){

        $rs=$this->orders();
        $headers =array("OrderID", "HasCustomerPaid","CustomerName","CustomerEmail",);
        return $this->export($rs, $headers,'Orders_');

    }

}
