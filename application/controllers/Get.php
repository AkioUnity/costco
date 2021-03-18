<?php

defined('BASEPATH') OR exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *');

class Get extends MY_Controller
{
    var $gmail_track_kind = array(
        '1' => 'Item Refund Credit',
        '2' => 'Price Change Credit',
        '3' => 'Tax Credit',
        '4' => 'Order Shipped'
    );

    public function __construct()
    {
        parent::__construct();
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        $this->load->model('get_model', '', TRUE);
        $this->load->helper('Costco');
    }

    public function login()
    {
        $username = $this->input->post("username");
        $password = $this->input->post("password"); //md5($this->input->post("password"));
        $query = "select * from users where name='$username' and passwd='$password' and status=0";
        $qresult = $this->get_model->get_item($query);
        if ($qresult) {
            $user_data = array(
                "id" => $qresult->id,
                "name" => $qresult->name,
                "level" => $qresult->level,
                "email" => $qresult->email
            );
            $this->session->set_userdata('backend021', $user_data);

            echo json_encode(array("success" => true, "msg" => ""));
        } else {
            echo json_encode(array("success" => false, "msg" => "Invalid Username or Password"));
        }
    }

    public function recover()
    {
        $email = $this->input->post("email");

        $query = "select * from users where email='$email' and status=0";
        $qresult = $this->get_model->get_item($query);
        if ($qresult) {

            $id = $qresult->id;
            $name = $qresult->name;
            $email = $qresult->email;
            $password = $this->random_pronounceable_word(rand(4, 7)) . substr(str_shuffle("0123456789"), 0, 3);
            $config = array(
                'mailtype' => 'html',
            );
            $subject = 'From myFarmer';
            $message = 'Hi, ' . $name . '<br/><br/>
			Your password of myFarmer keygen account is recovered<br/><br/>
			New passwrod is ' . $password . '<br/><br/>
			Best regards';

            $this->load->library('email', $config);
            $this->email->from('info@farmpro.com.br', 'myFarmer');
            $this->email->to($email);
            $this->email->subject($subject);
            $this->email->message($message);
            $this->email->send();

            $data = array("passwd" => md5($password));
            $this->get_model->update_item("users", $id, $data);

            echo json_encode(array("success" => true, "msg" => ""));
        } else {
            echo json_encode(array("success" => false, "msg" => "Can`t find this email"));
        }
    }

    private function random_pronounceable_word($length = 6)
    {

        // consonant sounds
        $cons = array(
            // single consonants. Beware of Q, it's often awkward in words
            'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
            'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'z',
            // possible combinations excluding those which cannot start a word
            'pt', 'gl', 'gr', 'ch', 'ph', 'ps', 'sh', 'st', 'th', 'wh',
        );

        // consonant combinations that cannot start a word
        $cons_cant_start = array(
            'ck', 'cm',
            'dr', 'ds',
            'ft',
            'gh', 'gn',
            'kr', 'ks',
            'ls', 'lt', 'lr',
            'mp', 'mt', 'ms',
            'ng', 'ns',
            'rd', 'rg', 'rs', 'rt',
            'ss',
            'ts', 'tch',
        );

        // wovels
        $vows = array(
            // single vowels
            'a', 'e', 'i', 'o', 'u', 'y',
            // vowel combinations your language allows
            'ee', 'oa', 'oo',
        );

        // start by vowel or consonant ?
        $current = (mt_rand(0, 1) == '0' ? 'cons' : 'vows');

        $word = '';

        while (strlen($word) < $length) {

            // After first letter, use all consonant combos
            if (strlen($word) == 2)
                $cons = array_merge($cons, $cons_cant_start);

            // random sign from either $cons or $vows
            $rnd = ${$current}[mt_rand(0, count(${$current}) - 1)];

            // check if random sign fits in word length
            if (strlen($word . $rnd) <= $length) {
                $word .= $rnd;
                // alternate sounds
                $current = ($current == 'cons' ? 'vows' : 'cons');
            }
        }

        return $word;
    }

    public function importList()
    {
        $config['upload_path'] = 'temp/';
        $config['allowed_types'] = 'xlsx';
        $config['encrypt_name'] = TRUE;
        $config['max_size'] = '2621440';

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('file')) {
            $result = false;
            $msg = $this->upload->display_errors();
        } else {
            $file_data = $this->upload->data();
            $file_path = 'temp/' . $file_data['file_name'];
            include 'Classes/PHPExcel/IOFactory.php';
            $inputFileName = $file_path;
            $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
            $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray("", true, false, true);
//            echo $allDataInSheet[1];
            $arrayCount = count($allDataInSheet);
            for ($i = 2; $i <= $arrayCount; $i++) {
                if (!strlen(trim($allDataInSheet[$i]["A"])))
                    continue;
                $data = array();
                $data["email"] = $allDataInSheet[$i]["A"];
                $data["order_id"] = $allDataInSheet[$i]["B"];
                $data["buyer_phone_number"] = $allDataInSheet[$i]["C"];
                $data["product_number"] = $allDataInSheet[$i]["D"];
                $data["product_name"] = $allDataInSheet[$i]["E"];
                $data["quantity_purchased"] = $allDataInSheet[$i]["F"];
                $data["ship_service"] = $allDataInSheet[$i]["G"];
                $data["recipient_name"] = $allDataInSheet[$i]["H"];
                $data["ship_address_1"] = $allDataInSheet[$i]["I"];
                $data["ship_address_2"] = $allDataInSheet[$i]["J"];
                $data["ship_city"] = $allDataInSheet[$i]["K"];
                $data["ship_state"] = $allDataInSheet[$i]["L"];
                $data["ship_postal_code"] = $allDataInSheet[$i]["M"];
                $costco_order_id = trim($allDataInSheet[$i]["N"]);
                if ($costco_order_id == '' || $costco_order_id == 0) {
                    $data["costco_order_id"] = 0;
                } else {
                    $data["costco_order_id"] = $costco_order_id;
                    $data["shipping_charge"] = $allDataInSheet[$i]["O"];
                    $data["tax"] = $allDataInSheet[$i]["P"];
                    $data["total"] = $allDataInSheet[$i]["Q"];
                    $data["created"] = $allDataInSheet[$i]["R"];
                    $data["track_number"] = $allDataInSheet[$i]["S"];
                    if (!$data["track_number"])
                        $data["track_number"] = "";
                }

                if (strtolower(trim($data['ship_service'])) == 'standard') {
                    $data['ship_service'] = 0;
                } else {
                    $data['ship_service'] = 1;
                }


//                echo json_encode(array('success' => false, 'msg' => $data));
                $cur_id=$this->get_model->checkExist2("orders", "order_id", $data["order_id"], "email", $data["email"]);
                if($cur_id) {
                    $this->get_model->update_table("orders", $cur_id, $data);
                }else{
                    $this->get_model->insert_table("orders", $data);
                }

                if(!$this->get_model->checkExist("waits", "email", $data["email"])) {
                    $this->get_model->insert_table("waits", array('email'=>$data["email"]));
                }
            }
            $result = true;
            $msg = '';
        }
        echo json_encode(array('success' => $result, 'msg' => $msg));
    }

    public function getOrderData()
    {
        $id = $this->input->post('id');
        $query = 'select * from orders where id=' . $id;
        $qresult = $this->get_model->get_item($query);
        echo json_encode($qresult);
    }

    public function updateOrder()
    {
        $data = $this->input->post("data");
        $id = $data["id"];
        unset($data["id"]);
        $this->get_model->update_item('orders', $id, $data);
        echo json_encode(array("success" => true, "msg" => $id));
    }

    public function getOrderList()
    {
        $result = array();
        $email=$this->input->post("email");
        $query = "select * from orders where (costco_order_id='' or costco_order_id=0) and email='".$email."'";
        $qresult = $this->get_model->get_list($query);
        $no = 0;
        foreach ($qresult as $item) {
            $id = $item->id;
            $no++;

            if ($item->ship_service == 0) {
                $ship_service = "Standard";
            } else {
                $ship_service = "Expedited";
            }
            $customer_detail = '<p><b>Name:</b> ' . $item->recipient_name . '</p>'
                . '<p><b>Address1:</b> ' . $item->ship_address_1 . '</p>'
                . '<p><b>Address2:</b> ' . $item->ship_address_2 . '</p>'
                . '<p><b>City:</b> ' . $item->ship_city . '</p>'
                . '<p><b>State:</b> ' . $item->ship_state . '</p>'
                . '<p><b>PostalCode:</b> ' . $item->ship_postal_code . '</p>'
                . '<p><b>Phone:</b> ' . $item->buyer_phone_number . '</p>';
            if ($item->costco_order_id == 0) {
                if ($item->order_msg == '') {
                    $order_detail = '';
                } else {
                    $order_detail = $item->order_msg;
                }
            } else {
                $order_detail = '<p><b>OrderId:</b> ' . $item->costco_order_id . '</p>'
                    . '<p><b>Shipping Charge:</b> ' . $item->shipping_charge . '</p>'
                    . '<p><b>Tax:</b> ' . $item->tax . '</p>'
                    . '<p><b>Total:</b> ' . $item->total . '</p>'
                    . '<p><b>Date:</b> ' . $item->created . '</p>';
            }

            $action = "<button class='btn btn-info btn-sm' data-toggle='tooltip' title='Edit Customer' onclick='edit($id);'><i class='fa fa-pencil-square'></i></button>";
            $action .= "<button class='btn btn-success btn-sm' data-toggle='tooltip' title='Place Order' onclick='makeOrder($id);'><i class='fa fa-shopping-cart'></i></button>";
            $action .= "<button class='btn btn-danger btn-sm' data-toggle='tooltip' title='Delete' onclick='del($id);'><i class='fa fa-times'></i></button>";
            $result[] = array(
                "id" => $item->id,
                "no" => $no,
                "order_id" => $item->order_id,
                "product_number" => $item->product_number,
                "quantity_purchased" => $item->quantity_purchased,
                "ship_service" => $ship_service,
                "customer_detail" => $customer_detail,
                "order_detail" => $order_detail,
                "action" => $action
            );
        }
        echo json_encode($result);
    }

    public function getPlacedOrderList()
    {
        $status = $this->input->post('status');
        $startdate = $this->input->post('startdate');
        $enddate = $this->input->post('enddate');
        $email=$this->input->post("email");
        $result = array();
        $query = "select * from orders where costco_order_id>0 and email='".$email."'";
        if ($status != 0) {
            if ($status == 1) {
                $query .= ' and length(trim(track_number))=0';
            } else {
                $query .= ' and length(trim(track_number))>0';
            }
        }
        if ($startdate != '') {
            $query .= ' and "' . $startdate . ' 00:00:00"<created and created<"' . $enddate . ' 23:59:59' . '"';
        }
        $query .= ' order by created desc';
        $qresult = $this->get_model->get_list($query);
        $no = 0;
        foreach ($qresult as $item) {
            $id = $item->id;
            $no++;

            if ($item->ship_service == 0) {
                $ship_service = "Standard";
            } else {
                $ship_service = "Expedited";
            }
            $customer_detail = '<p><b>Name:</b> ' . $item->recipient_name . '</p>'
                . '<p><b>Address1:</b> ' . $item->ship_address_1 . '</p>'
                . '<p><b>Address2:</b> ' . $item->ship_address_2 . '</p>'
                . '<p><b>City:</b> ' . $item->ship_city . '</p>'
                . '<p><b>State:</b> ' . $item->ship_state . '</p>'
                . '<p><b>PostalCode:</b> ' . $item->ship_postal_code . '</p>'
                . '<p><b>Phone:</b> ' . $item->buyer_phone_number . '</p>';
            if ($item->costco_order_id == 0) {
                if ($item->order_msg == '') {
                    $order_detail = '';
                } else {
                    $order_detail = $item->order_msg;
                }
            } else {
                $order_detail = '<p><b>OrderId:</b> ' . $item->costco_order_id . '</p>'
                    . '<p><b>Shipping Charge:</b> ' . $item->shipping_charge . '</p>'
                    . '<p><b>Tax:</b> ' . $item->tax . '</p>'
                    . '<p><b>Total:</b> ' . $item->total . '</p>'
                    . '<p><b>Date:</b> ' . $item->created . '</p>';
            }

            $action = "<button class='btn btn-success btn-sm' data-toggle='tooltip' title='Re-Order' onclick='makeOrder($id);'><i class='fa fa-shopping-cart'></i></button>";
            $action .= "<button class='btn btn-danger btn-sm' data-toggle='tooltip' title='Delete' onclick='del($id);'><i class='fa fa-times'></i></button>";
            $result[] = array(
                "id" => $item->id,
                "no" => $no,
                "order_id" => $item->order_id,
                "product_name" => '<p style="width: 250px;">' . $item->product_name . '</p>',
                "quantity_purchased" => $item->quantity_purchased,
                "ship_service" => $ship_service,
                "customer_detail" => $customer_detail,
                "order_detail" => $order_detail,
                "track_number" => $item->track_number,
                "etc" => '<p style="width: 150px;">' . $item->etc . '</p>',
                "action" => $action
            );
        }
        echo json_encode($result);
    }

    public function del()
    {
        $id = $this->input->post('id');
        $id = implode(",", $id);
        $query = "delete from orders where id in ($id)";
        $this->get_model->exec_query($query);
        echo json_encode(true);
    }

    public function makeOrder()
    {
        $id = $this->input->post('id');
        $id = implode(",", $id);
        $query = "select * from orders where id in ($id)";
        $qresult = $this->get_model->get_list($query);
        $item_list = array();
        foreach ($qresult as $item) {
            $item_list[] = (array)$item;
        }
        $msg = $this->order($item_list);
        if ($msg == '') {
            echo json_encode(array('success' => true, 'msg' => ''));
        } else {
            echo json_encode(array('success' => false, 'msg' => $msg));
        }
    }

    private function order($item_list)
    {
        //$this->load->library('session');
        error_reporting(E_ERROR);
        //require_once("Curl.class.php");
        //$curl = new Curl();

        $username = $this->config->item('username');
        $password = $this->config->item('password');
        $email = $this->config->item('email');
        $cvv = $this->config->item('cvv');
        $expire_month = $this->config->item('expire_month');
        $expire_year = $this->config->item('expire_year');
        $membershipNum = $this->config->item('membershipNum');
        $selfAddressId = $this->config->item('selfAddressId');
        $shipModeId_1 = $this->config->item('shipModeId_1');
        $shipModeId_2 = $this->config->item('shipModeId_2');

        $msg = '';

        //if (isset($_SESSION["control"])){
        //$session=$_SESSION["control"];
        //}else{
        $session = $this->init_costco_t();
        //$_SESSION["control"]=$session;
        //}
        //exit();
        foreach ($item_list as $order_item) {

            $id = $order_item['id'];
            $item_number = $order_item['product_number'];
            $quantity = $order_item['quantity_purchased'];
            $ship_service = $order_item['ship_service'];
            $name = $order_item['recipient_name'];
            $address1 = $order_item['ship_address_1'];
            $address2 = $order_item['ship_address_2'];
            $city = $order_item['ship_city'];
            $state = $order_item['ship_state'];
            $zip = $order_item['ship_postal_code'];
            $number = $order_item['buyer_phone_number'];

            $item_number_list = explode('-', $item_number);

            //$item_number = $item_number_list[0];
            //Getting cart
            //$content = $this->curl_get($session,'https://m.costco.com/CheckoutCartView?orderId=.&_pjax=true');
            //$dom = new DOMDocument();
            /// $dom->loadHTML($content);
            //$xpath = new DOMXPath($dom);
            //$results = $xpath->query("//div[@class='remove-link hidden-xs hidden-sm']");
            //For Emptying Cart
            //foreach ($results as $item) {
            //   $result = $xpath->query(".//a", $item);
            //   foreach ($result as $input) {
            //       $url = $input->getAttribute('href');
            //		echo "URL: https:" .$url. "</br>";
            //        $this->curl_get($session,'https:' . $url);
            //   }
            // }
            //Clean the Cart if there is something

            $this->clean_cart($session);

            if ($this->addtocart($session, $item_number, $item_number_list, $quantity,$zip) == true) {

                $this->startcheckout($session, $order_item);
            }


            $order_result = array('order_msg' => 'Unknown Error');
        }

        $session->quit();
    }

    private function init_costco_t()
    {

        require_once "vendor/autoload.php";

        $host = 'http://localhost:4444/wd/hub'; // this is the default
        $capabilities = Facebook\WebDriver\Remote\DesiredCapabilities::Chrome();
        $session = Facebook\WebDriver\Remote\RemoteWebDriver::create($host, $capabilities, 5000);
		$session->manage()->window()->maximize();

        $username = $this->config->item('username');
        $pass = $this->config->item('password');
        $session->get('https://m.costco.com/');

        $SignInLink = $session->findElement(Facebook\WebDriver\WebDriverBy::id('header_sign_in'));

        if (null === $SignInLink) {
			
			
			 $session->get('https://www.costco.com/LogonForm');
			 $SignInForm = $session->findElement(Facebook\WebDriver\WebDriverBy::id("LogonForm"));

            if (null === $SignInForm) {

                echo "Not Found";
            } else {
				
                $loginId = $session->findElement(Facebook\WebDriver\WebDriverBy::id("logonId"));
                $password = $session->findElement(Facebook\WebDriver\WebDriverBy::id("logonPassword"));
                $loginId->sendKeys($username);
                $password->sendKeys($pass);
				sleep(10);
                $SignInForm->submit();
            }
			

            //echo "Not Found";
        } else {

            //echo "Found Sign in Link .<br/>";
            //echo $session->getCurrentUrl();
			try {
				
			$SignInLink->click();	
			} catch (Facebook\WebDriver\Exception\ElementNotSelectableException $e) {
					$session->get('https://www.costco.com/LogonForm');


        } catch (Facebook\WebDriver\Exception\ElementNotVisibleException $e) {
				$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\ExpectedException $e) {
				$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\IMEEngineActivationFailedException $e) {
				$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\IMENotAvailableException $e) {
				$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\IndexOutOfBoundsException $e) {
				$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\InvalidCookieDomainException $e) {
				$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\InvalidCoordinatesException $e) {
				$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\InvalidElementStateException $e) {
				$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\InvalidSelectorException $e) {
				$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\MoveTargetOutOfBoundsException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NoAlertOpenException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NoCollectionException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NoScriptResultException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NoStringException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NoStringLengthException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NoStringWrapperException $e) { 	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NoSuchCollectionException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NoSuchDocumentException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NoSuchDriverException $e) { 	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NoSuchElementException $e) { 	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NoSuchFrameException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NoSuchWindowException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\NullPointerException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\ScriptTimeoutException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\SessionNotCreatedException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\StaleElementReferenceException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\TimeOutException $e) { 	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\UnableToSetCookieException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\UnexpectedAlertOpenException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\UnexpectedJavascriptException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\UnknownCommandException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\UnknownServerException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\UnrecognizedExceptionException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\WebDriverCurlException $e) {	$session->get('https://www.costco.com/LogonForm');

        } catch (Facebook\WebDriver\Exception\XPathLookupException $e) {	$session->get('https://www.costco.com/LogonForm');

        				
			
            }
			

            $SignInForm = $session->findElement(Facebook\WebDriver\WebDriverBy::id("LogonForm"));

            if (null === $SignInForm) {

                echo "Not Found";
            } else {


                $loginId = $session->findElement(Facebook\WebDriver\WebDriverBy::id("logonId"));
                $password = $session->findElement(Facebook\WebDriver\WebDriverBy::id("logonPassword"));
				//echo "The User name ". $username;
                $loginId->sendKeys($username);
                $password->sendKeys($pass);
				sleep(2);
                $SignInForm->submit();
            }
        }
        return $session;
    }

    private function clean_cart($session)
    {
        require_once "vendor/autoload.php";

        $CartLink = $session->findElement(Facebook\WebDriver\WebDriverBy::id("cart-d"));
        if (null === $CartLink) {

            //echo "Not Found";
        } else {

            $CartLink->click();
            //Xpath  of Remove
            // //*[@id="order-items-regular"]/div/div/div[8]/div[3]/a

            try {

                $RemoveLink = $session->findElement(Facebook\WebDriver\WebDriverBy::linkText("Remove")); // Empty Cart Page

                if (null === $RemoveLink) {

                } else {

                    $RemoveLink->click();
                }
            } catch (Facebook\WebDriver\Exception\NoSuchElementException $ex) {

            }
        }
    }

    private function addtocart($session, $item_number, $item_number_list, $quantity,$zip)
    {

        require_once "vendor/autoload.php";
        if (strlen($item_number_list[1]) > 3) {
            $product_url = 'http://m.costco.com/.product.' . $item_number_list[0] . '.html';
        } else {

            $product_url = 'http://m.costco.com/.product.' . $item_number . '.html';

        }

        $content = $this->curl_get($session, $product_url);

        //postal-popup-form   for zip code insertion   //feild postal-code-input

        sleep(4);
        try {

            $ProductForm = $session->findElement(Facebook\WebDriver\WebDriverBy::id("postal-popup-form"));
            $this->setvalue($session, "postal-code-input", $zip);
			$ProductForm->submit();
			sleep(4); //give it sometime to load after zip entry



        } catch (Facebook\WebDriver\Exception\ElementNotSelectableException $e) {


        } catch (Facebook\WebDriver\Exception\ElementNotVisibleException $e) {

        } catch (Facebook\WebDriver\Exception\ExpectedException $e) {

        } catch (Facebook\WebDriver\Exception\IMEEngineActivationFailedException $e) {

        } catch (Facebook\WebDriver\Exception\IMENotAvailableException $e) {

        } catch (Facebook\WebDriver\Exception\IndexOutOfBoundsException $e) {

        } catch (Facebook\WebDriver\Exception\InvalidCookieDomainException $e) {

        } catch (Facebook\WebDriver\Exception\InvalidCoordinatesException $e) {

        } catch (Facebook\WebDriver\Exception\InvalidElementStateException $e) {

        } catch (Facebook\WebDriver\Exception\InvalidSelectorException $e) {

        } catch (Facebook\WebDriver\Exception\MoveTargetOutOfBoundsException $e) {

        } catch (Facebook\WebDriver\Exception\NoAlertOpenException $e) {

        } catch (Facebook\WebDriver\Exception\NoCollectionException $e) {

        } catch (Facebook\WebDriver\Exception\NoScriptResultException $e) {

        } catch (Facebook\WebDriver\Exception\NoStringException $e) {

        } catch (Facebook\WebDriver\Exception\NoStringLengthException $e) {

        } catch (Facebook\WebDriver\Exception\NoStringWrapperException $e) {

        } catch (Facebook\WebDriver\Exception\NoSuchCollectionException $e) {

        } catch (Facebook\WebDriver\Exception\NoSuchDocumentException $e) {

        } catch (Facebook\WebDriver\Exception\NoSuchDriverException $e) {

        } catch (Facebook\WebDriver\Exception\NoSuchElementException $e) {

        } catch (Facebook\WebDriver\Exception\NoSuchFrameException $e) {

        } catch (Facebook\WebDriver\Exception\NoSuchWindowException $e) {

        } catch (Facebook\WebDriver\Exception\NullPointerException $e) {

        } catch (Facebook\WebDriver\Exception\ScriptTimeoutException $e) {

        } catch (Facebook\WebDriver\Exception\SessionNotCreatedException $e) {

        } catch (Facebook\WebDriver\Exception\StaleElementReferenceException $e) {

        } catch (Facebook\WebDriver\Exception\TimeOutException $e) {

        } catch (Facebook\WebDriver\Exception\UnableToSetCookieException $e) {

        } catch (Facebook\WebDriver\Exception\UnexpectedAlertOpenException $e) {

        } catch (Facebook\WebDriver\Exception\UnexpectedJavascriptException $e) {

        } catch (Facebook\WebDriver\Exception\UnknownCommandException $e) {

        } catch (Facebook\WebDriver\Exception\UnknownServerException $e) {

        } catch (Facebook\WebDriver\Exception\UnrecognizedExceptionException $e) {

        } catch (Facebook\WebDriver\Exception\WebDriverCurlException $e) {

        } catch (Facebook\WebDriver\Exception\XPathLookupException $e) {

        }



        //Search for product

        try {
            $ProductForm = $session->findElement(Facebook\WebDriver\WebDriverBy::id("ProductForm"));
        } catch (Facebook\WebDriver\Exception\ElementNotSelectableException $e) {

            return false;
        } catch (Facebook\WebDriver\Exception\ElementNotVisibleException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\ExpectedException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\IMEEngineActivationFailedException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\IMENotAvailableException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\IndexOutOfBoundsException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\InvalidCookieDomainException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\InvalidCoordinatesException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\InvalidElementStateException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\InvalidSelectorException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\MoveTargetOutOfBoundsException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NoAlertOpenException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NoCollectionException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NoScriptResultException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NoStringException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NoStringLengthException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NoStringWrapperException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NoSuchCollectionException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NoSuchDocumentException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NoSuchDriverException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NoSuchElementException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NoSuchFrameException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NoSuchWindowException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\NullPointerException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\ScriptTimeoutException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\SessionNotCreatedException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\StaleElementReferenceException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\TimeOutException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\UnableToSetCookieException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\UnexpectedAlertOpenException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\UnexpectedJavascriptException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\UnknownCommandException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\UnknownServerException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\UnrecognizedExceptionException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\WebDriverCurlException $e) {
            return false;
        } catch (Facebook\WebDriver\Exception\XPathLookupException $e) {
            return false;
        }
        if (null === $ProductForm) {

            echo "Product Form Not Found";
        } else {

            try {

                if (strlen($item_number_list[1]) > 3) { // there is something in the order id for variation

                    $Productv1 = new Facebook\WebDriver\WebDriverSelect($session->findElement(Facebook\WebDriver\WebDriverBy::id("productOption00")));
                    $Productv1->selectByValue($item_number_list[1]); //Comment this if you want to suppress selecting state by us

                }


                //productOption01


                if (strlen($item_number_list[2]) > 3) { // there is something in the order id for variation

                    $Productv1 = new Facebook\WebDriver\WebDriverSelect($session->findElement(Facebook\WebDriver\WebDriverBy::id("productOption01")));
                    $Productv1->selectByValue($item_number_list[2]); //Comment this if you want to suppress selecting state by us

                }


            } catch (Facebook\WebDriver\Exception\ElementNotSelectableException $e) {


            } catch (Facebook\WebDriver\Exception\ElementNotVisibleException $e) {

            } catch (Facebook\WebDriver\Exception\ExpectedException $e) {

            } catch (Facebook\WebDriver\Exception\IMEEngineActivationFailedException $e) {

            } catch (Facebook\WebDriver\Exception\IMENotAvailableException $e) {

            } catch (Facebook\WebDriver\Exception\IndexOutOfBoundsException $e) {

            } catch (Facebook\WebDriver\Exception\InvalidCookieDomainException $e) {

            } catch (Facebook\WebDriver\Exception\InvalidCoordinatesException $e) {

            } catch (Facebook\WebDriver\Exception\InvalidElementStateException $e) {

            } catch (Facebook\WebDriver\Exception\InvalidSelectorException $e) {

            } catch (Facebook\WebDriver\Exception\MoveTargetOutOfBoundsException $e) {

            } catch (Facebook\WebDriver\Exception\NoAlertOpenException $e) {

            } catch (Facebook\WebDriver\Exception\NoCollectionException $e) {

            } catch (Facebook\WebDriver\Exception\NoScriptResultException $e) {

            } catch (Facebook\WebDriver\Exception\NoStringException $e) {

            } catch (Facebook\WebDriver\Exception\NoStringLengthException $e) {

            } catch (Facebook\WebDriver\Exception\NoStringWrapperException $e) {

            } catch (Facebook\WebDriver\Exception\NoSuchCollectionException $e) {

            } catch (Facebook\WebDriver\Exception\NoSuchDocumentException $e) {

            } catch (Facebook\WebDriver\Exception\NoSuchDriverException $e) {

            } catch (Facebook\WebDriver\Exception\NoSuchElementException $e) {

            } catch (Facebook\WebDriver\Exception\NoSuchFrameException $e) {

            } catch (Facebook\WebDriver\Exception\NoSuchWindowException $e) {

            } catch (Facebook\WebDriver\Exception\NullPointerException $e) {

            } catch (Facebook\WebDriver\Exception\ScriptTimeoutException $e) {

            } catch (Facebook\WebDriver\Exception\SessionNotCreatedException $e) {

            } catch (Facebook\WebDriver\Exception\StaleElementReferenceException $e) {

            } catch (Facebook\WebDriver\Exception\TimeOutException $e) {

            } catch (Facebook\WebDriver\Exception\UnableToSetCookieException $e) {

            } catch (Facebook\WebDriver\Exception\UnexpectedAlertOpenException $e) {

            } catch (Facebook\WebDriver\Exception\UnexpectedJavascriptException $e) {

            } catch (Facebook\WebDriver\Exception\UnknownCommandException $e) {

            } catch (Facebook\WebDriver\Exception\UnknownServerException $e) {

            } catch (Facebook\WebDriver\Exception\UnrecognizedExceptionException $e) {

            } catch (Facebook\WebDriver\Exception\WebDriverCurlException $e) {

            } catch (Facebook\WebDriver\Exception\XPathLookupException $e) {

            }

            try {
                $QtyInput = $session->findElement(Facebook\WebDriver\WebDriverBy::id("minQtyText"));
                $QtyInput->clear();
                $QtyInput->sendkeys($quantity);
                $ProductForm->submit();

                sleep(6);
                $RemoveLink = $session->findElement(Facebook\WebDriver\WebDriverBy::linkText("View Cart")); // Moving to Cart Page


                if (null === $RemoveLink) {

                } else {

                    $RemoveLink->click();
                }


            } catch (Facebook\WebDriver\Exception\ElementNotSelectableException $e) {

                return false;
            } catch (Facebook\WebDriver\Exception\ElementNotVisibleException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\ExpectedException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\IMEEngineActivationFailedException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\IMENotAvailableException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\IndexOutOfBoundsException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\InvalidCookieDomainException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\InvalidCoordinatesException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\InvalidElementStateException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\InvalidSelectorException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\MoveTargetOutOfBoundsException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NoAlertOpenException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NoCollectionException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NoScriptResultException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NoStringException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NoStringLengthException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NoStringWrapperException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NoSuchCollectionException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NoSuchDocumentException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NoSuchDriverException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NoSuchElementException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NoSuchFrameException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NoSuchWindowException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\NullPointerException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\ScriptTimeoutException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\SessionNotCreatedException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\StaleElementReferenceException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\TimeOutException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\UnableToSetCookieException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\UnexpectedAlertOpenException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\UnexpectedJavascriptException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\UnknownCommandException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\UnknownServerException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\UnrecognizedExceptionException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\WebDriverCurlException $e) {
                return false;
            } catch (Facebook\WebDriver\Exception\XPathLookupException $e) {
                return false;
            }
            return true;
        }
    }

    function curl_get($session, $url)
    {
        require_once "vendor/autoload.php";
        $session->get($url);
        sleep(5);
        $page = $session->getPageSource();
        $content = $page;
        return $content;
    }

    private function startcheckout($session, $order_item)
    {
        require_once "vendor/autoload.php";

        $username = $this->config->item('username');
        $password = $this->config->item('password');
        $email = $this->config->item('email');
        $cvv = $this->config->item('cvv');
        $expire_month = $this->config->item('expire_month');
        $expire_year = $this->config->item('expire_year');
        $membershipNum = $this->config->item('membershipNum');
        $selfAddressId = $this->config->item('selfAddressId');
        $shipModeId_1 = $this->config->item('shipModeId_1');
        $shipModeId_2 = $this->config->item('shipModeId_2');

        $id = $order_item['id'];
        $item_number = $order_item['product_number'];
        $quantity = $order_item['quantity_purchased'];
        $ship_service = $order_item['ship_service'];
        $name = $order_item['recipient_name'];
        $address1 = $order_item['ship_address_1'];
        $address2 = $order_item['ship_address_2'];
        $city = $order_item['ship_city'];
        $state = $order_item['ship_state'];
        $zip = $order_item['ship_postal_code'];
        $number = $order_item['buyer_phone_number'];

        // $session->get("https://m.costco.com/CheckoutCartView"); Extra Refresh;
        // $session->refresh();
        //$session->refresh();
        //sleep(3); // Instead of waiting lets check if the element is present
        $session->wait(10)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::id("shopCartCheckoutSubmitButton")));
        $CartLink = $session->findElement(Facebook\WebDriver\WebDriverBy::id("shopCartCheckoutSubmitButton"));

        //shipModeId_1

        if ($ship_service == 1) {
            $session->wait(60)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::cssSelector('input[type="radio"][name="shipModeId_1"]')));
            foreach ($session->findElements(Facebook\WebDriver\WebDriverBy::cssSelector('input[type="radio"][name="shipModeId_1"]')) as $radio) {
                if ($radio->getAttribute('value') == "11153") {
                    $radio->click();
                    break;
                }
            }
        }

        if ($CartLink)
            sleep(3); //kevin added
        $CartLink->click();
        //sleep(3); // Wait for the Page Load // again donot wait just check if the element is present 


        do {
            try {


                if (strstr($session->getCurrentURL(), "CheckoutShippingView")) {

                    $session->wait(10)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::id("firstId"))); //We asked it to wait for next action untill we have this
                    $name_split = explode(' ', $name);
                    $fName = $name_split[0];
                    $lName = trim(str_replace($fName, '', $name)); //see this line it replaces the last name from name so if they are same it was already eplaced
                    if ($name == "")
                        $name = "A";
                    if ($lName == "")
                        $lName = "A";
                    $this->setvalue($session, "firstId", $fName);
                    $this->setvalue($session, "lastId", $lName);
                    $this->setvalue($session, "address1Id", $address1);
                    $this->setvalue($session, "address2Id", $address2);
                    $zip = str_replace("-", "", $zip);

                    //$this->setvalue($session, "postalId", substr($zip, 0, 3)); //this should increase the typing speed

                    for ($i = 0; $i <= strlen($zip); $i++) {

                        $this->setvalue($session, "postalId", substr($zip, $i, 1));
                        sleep(1); // Waiting 1 secfor the page to complete AJAX
                    }
                    sleep(2);
                    $StateField = new Facebook\WebDriver\WebDriverSelect($session->findElement(Facebook\WebDriver\WebDriverBy::id("stateId")));
                    $StateField->selectByValue($state); //Comment this if you want to suppress selecting state by us
                    //cityId
                    if (strlen($session->findElement(Facebook\WebDriver\WebDriverBy::id("cityId"))->getAttribute("value")) < 3)
                        $this->setvalue($session, "cityId", $city); //Comment this if you want to suppress selecting city by us
                    $this->setvalue($session, "emailId", "gtscostco@gmail.com");
                    //sleep(2);
                    //$this->setvalue($session, "phoneId", "" . $number); Commented due to Garbage enty
                    $number = str_replace("-", "", $number);
                    //$this->setvalue($session, "phoneId", substr($number, 0, 2));

                    for ($i = 0; $i <= strlen($number); $i++) {
                        //sleep(1);
                        $this->setvalue($session, "phoneId", substr($number, $i, 1));
                    }
					
                    //$SField = $session->findElement(Facebook\WebDriver\WebDriverBy::xpath('//*[@id="address-modal-inline"]/div[10]/div[1]/div/label/span'));
					$SField = $session->findElement(Facebook\WebDriver\WebDriverBy::xpath('//*[@id="address-modal-inline"]/div[15]/div[1]/div/label/span'));
                    $SField->click();
                    //sleep(3); //Note this line number decrease 10 to 5 or 2 to increase speed
                    $session->wait(10)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::name("place-order")));
                    $Field = $session->findElement(Facebook\WebDriver\WebDriverBy::name("place-order"));
                    if ($Field)
                        $Field->click();
                    sleep(4);
                    // //*[@id="costcoModalBtn2"]
                    // //*[@id="entered-address"]/input
				try {
					  $session->wait(60)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::cssSelector('input[type="radio"][name="verify"]')));
                        foreach ($session->findElements(Facebook\WebDriver\WebDriverBy::cssSelector('input[type="radio"][name="verify"]')) as $radio) {
                            if ($radio->getAttribute('value') == "entered") {
                                $radio->click();
                                break;
                            }
                        }
                        //exit();
                        $session->wait(60)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::xpath('//*[@id="costcoModalBtn2"]')));
                        $SField = $session->findElement(Facebook\WebDriver\WebDriverBy::xpath('//*[@id="costcoModalBtn2"]'));
                        $SField->click();
                    

                        $session->wait(60)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::cssSelector('input[type="radio"][name="verify"]')));
                        foreach ($session->findElements(Facebook\WebDriver\WebDriverBy::cssSelector('input[type="radio"][name="verify"]')) as $radio) {
                            if ($radio->getAttribute('value') == "entered") {
                                $radio->click();
                                break;
                            }
                        }
                        //exit();
						// if theere is address we select and press okay here
						$session->wait(60)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::xpath('//*[@id="costcoModalBtn2"]')));
                        $SField = $session->findElement(Facebook\WebDriver\WebDriverBy::xpath('//*[@id="costcoModalBtn2"]'));
                        $SField->click();
                    } catch (Facebook\WebDriver\Exception\ElementNotSelectableException $e) {

                    } catch (Facebook\WebDriver\Exception\ElementNotVisibleException $e) {

                    } catch (Facebook\WebDriver\Exception\ExpectedException $e) {

                    } catch (Facebook\WebDriver\Exception\IMEEngineActivationFailedException $e) {

                    } catch (Facebook\WebDriver\Exception\IMENotAvailableException $e) {

                    } catch (Facebook\WebDriver\Exception\IndexOutOfBoundsException $e) {

                    } catch (Facebook\WebDriver\Exception\InvalidCookieDomainException $e) {

                    } catch (Facebook\WebDriver\Exception\InvalidCoordinatesException $e) {

                    } catch (Facebook\WebDriver\Exception\InvalidElementStateException $e) {

                    } catch (Facebook\WebDriver\Exception\InvalidSelectorException $e) {

                    } catch (Facebook\WebDriver\Exception\MoveTargetOutOfBoundsException $e) {

                    } catch (Facebook\WebDriver\Exception\NoAlertOpenException $e) {

                    } catch (Facebook\WebDriver\Exception\NoCollectionException $e) {

                    } catch (Facebook\WebDriver\Exception\NoScriptResultException $e) {

                    } catch (Facebook\WebDriver\Exception\NoStringException $e) {

                    } catch (Facebook\WebDriver\Exception\NoStringLengthException $e) {

                    } catch (Facebook\WebDriver\Exception\NoStringWrapperException $e) {

                    } catch (Facebook\WebDriver\Exception\NoSuchCollectionException $e) {

                    } catch (Facebook\WebDriver\Exception\NoSuchDocumentException $e) {

                    } catch (Facebook\WebDriver\Exception\NoSuchDriverException $e) {

                    } catch (Facebook\WebDriver\Exception\NoSuchElementException $e) {

                    } catch (Facebook\WebDriver\Exception\NoSuchFrameException $e) {

                    } catch (Facebook\WebDriver\Exception\NoSuchWindowException $e) {

                    } catch (Facebook\WebDriver\Exception\NullPointerException $e) {

                    } catch (Facebook\WebDriver\Exception\ScriptTimeoutException $e) {

                    } catch (Facebook\WebDriver\Exception\SessionNotCreatedException $e) {

                    } catch (Facebook\WebDriver\Exception\StaleElementReferenceException $e) {

                    } catch (Facebook\WebDriver\Exception\TimeOutException $e) {

                    } catch (Facebook\WebDriver\Exception\UnableToSetCookieException $e) {

                    } catch (Facebook\WebDriver\Exception\UnexpectedAlertOpenException $e) {

                    } catch (Facebook\WebDriver\Exception\UnexpectedJavascriptException $e) {

                    } catch (Facebook\WebDriver\Exception\UnknownCommandException $e) {

                    } catch (Facebook\WebDriver\Exception\UnknownServerException $e) {

                    } catch (Facebook\WebDriver\Exception\UnrecognizedExceptionException $e) {

                    } catch (Facebook\WebDriver\Exception\WebDriverCurlException $e) {

                    } catch (Facebook\WebDriver\Exception\XPathLookupException $e) {

                    }


                    //	 sleep(6);// wait for the iframe to load in full 5 secs

                    sleep(4); //Why Sleep
                    // //*[@id="radio-credit-card"]  or //*[@id="credit-card-block"]/div[1]/div[1]/div/div/label //radio-credit-card
					// we are waiting here 60 secs to make sure page is perfectly loaded
                    //echo "I am here";
					//exit();
					$session->wait(60)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::id("radio-credit-card")));
                    $CField = $session->findElement(Facebook\WebDriver\WebDriverBy::className('control--radio'));
                    $CField->click();


                    // //*[@id="cc-payment-block"]/div[1]/div[2]/div[2]/div/iframe
                    sleep(2);
                    $session->wait(10)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::xpath('//*[@id="cc-payment-block"]/div[1]/div[2]/div[2]/div/iframe')));
                    $session->switchTo()->frame($session->findElement(Facebook\WebDriver\WebDriverBy::xpath('//*[@id="cc-payment-block"]/div[1]/div[2]/div[2]/div/iframe')));


                    $CV = $session->findElement(Facebook\WebDriver\WebDriverBy::xpath("//input[@type='tel']"));
                    //var_dump($CV);
                    $CV->sendKeys($cvv);
                    $session->switchTo()->defaultContent();
                    // //*[@id="order-summary-body"]/input
					sleep(2); //kevin added
                    $Field = $session->findElement(Facebook\WebDriver\WebDriverBy::name("place-order"));
                    if ($Field)
                        $Field->click();
                    //sleep(2); no wait just go 
                    sleep(6); // wait 5 secs to tha the order information is loaded properly before placing order
                    $session->wait(60)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::name("orderId")));
                    $Field = $session->findElement(Facebook\WebDriver\WebDriverBy::name("orderId"));
                    $order_result = array('order_msg' => 'Unknown Error');
                    $orderId = $Field->getAttribute("value");


                    $shipping_charge = "";
                    $tax = "";
                    $order_total = "";
                    $session->wait(60)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::xpath('//*[@id="order-summary-body"]/dl[1]/dd[2]')));
                    $shipping_item = trim($session->findElement(Facebook\WebDriver\WebDriverBy::xpath('//*[@id="order-summary-body"]/dl[1]/dd[2]'))->getText());
                    $shipping_charge = str_replace('$', '', $shipping_item);

                    // //*[@id="order-summary-body"]/dl[1]/dd[3]
                    $tax_item = trim($session->findElement(Facebook\WebDriver\WebDriverBy::xpath('//*[@id="order-summary-body"]/dl[1]/dd[3]'))->getText());
                    $tax = str_replace('$', '', $tax_item);

                    //  outstandingPrincipal
                    $total_item = $session->findElement(Facebook\WebDriver\WebDriverBy::name("outstandingPrincipal"))->getAttribute("value");
                    $order_total = str_replace('$', '', $total_item);
                    //END DL CONTAINERS 


                    $order_result['order_msg'] = '';
                    $order_result['etc'] = $etc;
                    $order_result['costco_order_id'] = $orderId;
                    $order_result['shipping_charge'] = $shipping_charge;
                    $order_result['tax'] = $tax;
                    $order_result['total'] = $order_total;
                    $order_result['created'] = date('Y-m-d H:i:s');


                    $this->get_model->update_item('orders', $id, $order_result);
                    //exit();
                    //sleep(4);
                    $session->wait(20)->until(Facebook\WebDriver\WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(Facebook\WebDriver\WebDriverBy::name("place-order")));
                    $Field = $session->findElement(Facebook\WebDriver\WebDriverBy::name("place-order"));
					sleep(2);
                    if ($Field)
                        $Field->click(); // This will Place Order
					sleep(10);
//$session->stop(); //Now it will close the window automatically
                    // //No wait
                    break;
                } elseif (strstr($session->getCurrentUrl(), "Logon")) {
                    $this->retry_login($session);
                }
            } catch (Exception $e) {

                // We have a problem try to skip over
                break;
            } catch (Facebook\WebDriver\Exception\ElementNotSelectableException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\ElementNotVisibleException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\ExpectedException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\IMEEngineActivationFailedException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\IMENotAvailableException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\IndexOutOfBoundsException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\InvalidCookieDomainException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\InvalidCoordinatesException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\InvalidElementStateException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\InvalidSelectorException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\MoveTargetOutOfBoundsException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NoAlertOpenException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NoCollectionException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NoScriptResultException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NoStringException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NoStringLengthException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NoStringWrapperException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NoSuchCollectionException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NoSuchDocumentException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NoSuchDriverException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NoSuchElementException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NoSuchFrameException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NoSuchWindowException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\NullPointerException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\ScriptTimeoutException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\SessionNotCreatedException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\StaleElementReferenceException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\TimeOutException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\UnableToSetCookieException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\UnexpectedAlertOpenException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\UnexpectedJavascriptException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\UnknownCommandException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\UnknownServerException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\UnrecognizedExceptionException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\WebDriverCurlException $e) {
                break;
            } catch (Facebook\WebDriver\Exception\XPathLookupException $e) {
                break;
            }
        } while (true);
        $session->get("https://m.costco.com/");
        // sleep(1); No wait
        //echo "Loop Breaked";
    }

    private function setvalue($session, $id, $value)
    {
        require_once "vendor/autoload.php";

        $CartLink = $session->findElement(Facebook\WebDriver\WebDriverBy::id($id));
        sleep(1);
        if ($CartLink)
            $CartLink->sendKeys($value);
    }

    function retry_login($session)
    {

        require_once "vendor/autoload.php";

        $username = $this->config->item('username');
        $pass = $this->config->item('password');

        //$page = $session->getPage();
        //var_dump($page);
        $SignInForm = $session->findElement(Facebook\WebDriver\WebDriverBy::id("LogonForm"));

        if (null === $SignInForm) {

            echo "Not Found";
        } else {


            $loginId = $session->findElement(Facebook\WebDriver\WebDriverBy::id("logonId"));
            $password = $session->findElement(Facebook\WebDriver\WebDriverBy::id("logonPassword"));
            $loginId->sendKeys($username);
            $password->sendKeys($pass);
            $SignInForm->submit();
        }
    }

    public function cleanCart()
    {
        error_reporting(E_ERROR);

        require_once("Curl.class.php");
        //require_once("Curl.class.php");

        $curl = new Curl();

        $username = $this->config->item('username');
        $password = $this->config->item('password');

        $msg = "";
        do {
            //$curl->get('https://m.costco.com/Logoff');
            //$content = $curl->get('https://m.costco.com/LogonForm');
            //$content = $curl->get('https://m.costco.com/LogonForm?URL=LogonForm&_pjax=true');//
            //$dom = new DOMDocument();
            //$dom->loadHTML($content);
            //$xpath = new DOMXPath($dom);
            //$results = $xpath->query("//form[@id='LogonForm']");
            //if ($results->length == 0) {
            //    $msg = "can`t load login page";
            //    break;
            // }
            // $login_data = array();
            // foreach ($results as $item) {
            //     $result = $xpath->query(".//input[@type='hidden']", $item);
            //     foreach ($result as $input) {
            //         $login_data[$input->getAttribute('name')] = $input->getAttribute('value');
            //     }
            //     $login_data['logonId'] = $username;
            //     $login_data['logonPassword'] = $password;
            //     $login_data['option1'] = 'on';
            //     break;
            //  }
            //$content = $curl->post('https://m.costco.com/Logon', $login_data);
            //$response = $curl->getResponse();
            //if (strpos($response['url'], 'catalogId')) {
            //     $msg = "login failed";
            //     break;
            // }

            $session = $this->init_costco();
            $content = $this->curl_get($session, 'https://m.costco.com/CheckoutCartView?orderId=.&_pjax=true');

            $dom = new DOMDocument();
            $dom->loadHTML($content);
            $xpath = new DOMXPath($dom);
            $results = $xpath->query("//div[@class='remove-link hidden-xs hidden-sm']");
            foreach ($results as $item) {
                $result = $xpath->query(".//a", $item);
                foreach ($result as $input) {
                    $url = $input->getAttribute('href');
                    $content = $this->curl_get($session, 'https:' . $url);
                }
            }
        } while (false);
        //$curl->get('https://m.costco.com/Logoff');
        //$curl->close();
        $session->stop();

        if ($msg == "") {
            echo json_encode(array('success' => true, 'msg' => ''));
        } else {
            echo json_encode(array('success' => false, 'msg' => $msg));
        }
    }

	
	private function retryloginmink($session){
		
			sleep(2);
			 $username = $this->config->item('username');
        $pass = $this->config->item('password');
			 $page = $session->getPage();
		  $SignInForm = $page->find('named', array('id', "LogonForm"));

            if (null === $SignInForm) {

                echo "Not Found";
            } else {

                //echo " Form Found <br/>";
                $loginId = $page->find('named', array('id', "logonId"));
                $password = $page->find('named', array('id', "logonPassword"));
                $loginId->setValue($username);
                $password->setValue($pass);
                //echo " Login and Password Added<br/>";
                $SignInForm->submit();
                //echo $session->getCurrentUrl();
                //echo " Form Submit <br/>";
                $page = $session->getPage();
                //var_dump($page);
                //var_dump($page->getHtml());
            }
	}
	
    private function init_costco()
    {

        require_once "vendor/autoload.php";

        $driver = new \Behat\Mink\Driver\Selenium2Driver('chrome');

        $session = new \Behat\Mink\Session($driver);
		
		

        $session->start();
		$session->getDriver()->maximizeWindow();
        $username = $this->config->item('username');
        $pass = $this->config->item('password');
        //$username='gtscostco+50@gmail.com';
        //$pass='Costco12345';
        $session->visit('https://m.costco.com/');
        $page = $session->getPage();


        $SignInLink = $page->find('named', array('id', "header_sign_in"));
        if (null === $SignInLink) {

            //echo "Not Found";
        } else {

            //echo "Found Sign in Link .<br/>";
            //echo $session->getCurrentUrl();

            $SignInLink->click();
            //echo "Clicked Sign in Link .<br/>";
            //echo $session->getCurrentUrl();

            $page = $session->getPage();
            //var_dump($page);
            $SignInForm = $page->find('named', array('id', "LogonForm"));

            if (null === $SignInForm) {

                echo "Not Found";
            } else {

                //echo " Form Found <br/>";
                $loginId = $page->find('named', array('id', "logonId"));
                $password = $page->find('named', array('id', "logonPassword"));
                $loginId->setValue($username);
                $password->setValue($pass);
                //echo " Login and Password Added<br/>";
                $SignInForm->submit();
                //echo $session->getCurrentUrl();
                //echo " Form Submit <br/>";
                $page = $session->getPage();
                //var_dump($page);
                //var_dump($page->getHtml());
            }
        }
        return $session;
    }

    public function cs()
    {
        $this->load->library('session');
        $this->session->unset_userdata($_SESSION['control']);
    }

    public function updateTrack()
    {
        $query = "select id, costco_order_id from orders where costco_order_id>0 and (length(track_number)<10 or track_number='Doesn`t exist')";
        $qresult = $this->get_model->get_list($query);
        $item_list = array();
        foreach ($qresult as $item) {
            $item_list[] = array('id' => $item->id, 'costco_order_id' => $item->costco_order_id);
        }
        $msg = $this->track($item_list);
        if ($msg == '') {
            echo json_encode(array('success' => true, 'msg' => ''));
        } else {
            echo json_encode(array('success' => false, 'msg' => $msg));
        }
    }

    private function track($item_list)
    {
        error_reporting(E_ERROR);
        $session = $this->init_costco();

        //require_once("Curl.class.php");
        //$curl = new Curl();

        $username = $this->config->item('username');
        $password = $this->config->item('password');
        $msg = '';
        do {

            foreach ($item_list as $order_item) {
                $id = $order_item['id'];
                //echo "Ting;";
                $costco_order_id = $order_item['costco_order_id'];
                do {

                    //sleep(2);
                    $product_url = 'https://m.costco.com/OrderStatusDetailsView?langId=-1&storeId=10301&catalogId=10701&orderId=' . $costco_order_id;

                    //$content = $curl->get($product_url);
                    $session->visit($product_url);
					
					if (strstr($session->getCurrentUrl(), "Logon")) {
                        $this->retryloginmink($session);
					//	echo "Trying";
                    }
                    //echo $session->getCurrentUrl();
                    //					sleep(5);
                    // https://www.costco.com/OrderStatusDetailsView?langId=-1&storeId=10301&catalogId=10701&orderId=675254345
                    sleep(2);  //changed from 5 to 2
								
					
                    if (strstr($session->getCurrentUrl(), "OrderStatusDetailsView")) {
                        $session->getCurrentUrl();
                        //sleep(2);
                        $page = $session->getPage();
                        $content = $page->getHtml();
                    
					//$order = $session->getPage()->find('xpath', '//*[@id="order-details-wrapper"]/div/div[1]/div/div/div[2]/div[1]/p');
					



					$dom = new DOMDocument();
                        $dom->loadHTML($content);
						if (strstr($content,"Cancelled")){
							
							$track_number="Cancelled";
							break;
							
						}
                        $xpath = new DOMXPath($dom);
						
						
						$o= strstr($content,"Order Number");
						
						$o= substr($o,12,stripos($o,"</div>"));
						$o=strip_tags($o);
						$o=trim($o);
						//echo "the string " . $o;
						
						if (strlen($o)<8){
							$track_number="No Order";
							break;
						}
						
						
						
                        $results = $xpath->query("//div[@class='shipment_details']");
                        //$results = $xpath->query("//td[@class='order-line-item']");
                        if ($results->length == 0) {
                            $track_number = '';
                            $track_number = "";
                            break;
                        }
                        $result = $xpath->query(".//a", $results->item(0));
                        if ($result->length == 0) {
                            $track_number = '';
                            break;
                        }
                        if ($result->item(0)->getAttribute('href') == '#') {
                            $track_number_list = array();
                            $results1 = $xpath->query("//div[@class='shipment-info']");
                            foreach ($results1 as $item1) {
                                $result1 = $xpath->query(".//a", $item1);
                                if ($result1->length > 0) {
                                    $track_number_list[] = $result1->item(0)->textContent;
                                }
                            }
                            $track_number = implode(',', $track_number_list);
                        } else {
                            $track_number = $result->item(0)->textContent;
                        }
                        break;
						
                    } elseif (strstr($session->getCurrentUrl(), "Logon")) {
                        $this->retryloginmink($session);
                    }

                } while (true);
				
				//echo "The track Number is here" . $track_number;
                if (trim($track_number) != '') {
					//$track_number.="W"; 
					 $this->get_model->update_item('orders', $id, array('track_number' => $track_number));
                }else{
					//=$session->getPage()->getValue("orderId");
					
				//	$track_number="Doesn`t exist";
					
					$this->get_model->update_item('orders', $id, array('track_number' => $track_number));
					
				}
            }
        } while (false);
        //$curl->get('https://m.costco.com/Logoff');
        //$curl->close();
        //
		$session->stop(); //Here it was commented
        return $msg;
    }

    public function exportList()
    {
        include 'Classes/PHPExcel/IOFactory.php';
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'order-id');
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, 'buyer-phone-number');
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, 'product-number');
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, 'product-name');
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(70);
        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, 'quantity-purchased');
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, 'ship-service');
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, 'recipient-name');
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
        $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, 'ship-address-1');
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(40);
        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, 'ship-address-2');
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(40);
        $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, 'ship-city');
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, 'ship-state');
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, 'ship-postal-code');
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('M' . $rowCount, 'costco-order-id');
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('N' . $rowCount, 'shipping-charge');
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('O' . $rowCount, 'tax');
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('P' . $rowCount, 'total');
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('Q' . $rowCount, 'ordered');
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('R' . $rowCount, 'track-number');
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(50);
        $objPHPExcel->getActiveSheet()->SetCellValue('S' . $rowCount, 'etc');
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(100);


        $status = $this->input->post('status');
        $startdate = $this->input->post('startdate');
        $enddate = $this->input->post('enddate');
        $query = "select * from orders where costco_order_id>0";
        if ($status != 0) {
            if ($status == 1) {
                $query .= ' and length(track_number)=0';
            } else {
                $query .= ' and length(track_number)>0';
            }
        }
        if ($startdate != '') {
            $query .= ' and "' . $startdate . ' 00:00:00"<created and created<"' . $enddate . ' 23:59:59' . '"';
        }
        $qresult = $this->get_model->get_list($query);
        foreach ($qresult as $item) {
            $row = (array)$item;
            $rowCount++;
            if ($row['ship_service'] == 0) {
                $row['ship_service'] = "Standard";
            } else {
                $row['ship_service'] = "Expedited";
            }
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $row["order_id"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $row["buyer_phone_number"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $row["product_number"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $row["product_name"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $row["quantity_purchased"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $row["ship_service"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $row["recipient_name"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $row["ship_address_1"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $row["ship_address_2"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $row["ship_city"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $row["ship_state"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $row["ship_postal_code"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('M' . $rowCount, $row["costco_order_id"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('N' . $rowCount, str_replace("$", "", $row["shipping_charge"]));
            $objPHPExcel->getActiveSheet()->SetCellValue('O' . $rowCount, str_replace("$", "", $row["tax"]));
            $objPHPExcel->getActiveSheet()->SetCellValue('P' . $rowCount, str_replace("$", "", $row["total"]));
            $objPHPExcel->getActiveSheet()->SetCellValue('Q' . $rowCount, $row["created"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('R' . $rowCount, $row["track_number"] . "  ");
            $objPHPExcel->getActiveSheet()->SetCellValue('S' . $rowCount, $row["etc"]);
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $file_path = 'temp/' . date('Y-m-d') . "-" . uniqid() . ".xlsx";
        $objWriter->save($file_path);
        $result = true;
        $msg = $file_path;
        echo json_encode(array('success' => $result, 'msg' => $msg));
    }

    public function importPriceList()
    {
        $config['upload_path'] = 'temp/';
        $config['allowed_types'] = 'xlsx';
        $config['encrypt_name'] = TRUE;
        $config['max_size'] = '2621440';
        $email = $this->input->post('email');
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('file')) {
            $result = false;
            $msg = $this->upload->display_errors();
        } else {
            $file_data = $this->upload->data();
            $file_path = 'temp/' . $file_data['file_name'];
            include 'Classes/PHPExcel/IOFactory.php';
            $inputFileName = $file_path;
            $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
            $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray("", true, false, true);
            $arrayCount = count($allDataInSheet);
            for ($i = 2; $i <= $arrayCount; $i++) {
                if (!strlen(trim($allDataInSheet[$i]["A"])))
                    continue;
                $data = array();
                $data["asin"] = $allDataInSheet[$i]["A"];
                $data["title"] = $allDataInSheet[$i]["B"];
                $data["costco_number"] = $allDataInSheet[$i]["C"];
                $data["regular_price"] = $allDataInSheet[$i]["D"];
                $data["s_h"] = $allDataInSheet[$i]["E"];
                $data["coupon"] = $allDataInSheet[$i]["F"];
                $data["final_price"] = $allDataInSheet[$i]["G"];
                $data["coupon_start"] = $allDataInSheet[$i]["H"];
                $data["coupon_end"] = $allDataInSheet[$i]["I"];
                $data["out_of_stock"] = $allDataInSheet[$i]["J"];
                $data["email"] = $email;
                $this->get_model->insert_table("prices", $data);
            }
            $result = true;
            $msg = '';
        }
        echo json_encode(array('success' => $result, 'msg' => $msg));
    }

    public function getPriceList()
    {
        $result = array();
        $email=$this->input->post("email");
        $query = "select * from prices where email='".$email."'";
        $qresult = $this->get_model->get_list($query);
        $no = 0;
        foreach ($qresult as $item) {
            $id = $item->id;
            $no++;

            $action = "<button class='btn btn-success btn-sm' data-toggle='tooltip' title='Check price' onclick='makeCheck($id);'><i class='fa fa-search'></i></button>";
            $action .= "<button class='btn btn-danger btn-sm' data-toggle='tooltip' title='Delete' onclick='delPrice($id);'><i class='fa fa-times'></i></button>";
            $result[] = array(
                "id" => $item->id,
                "no" => $no,
                "asin" => $item->asin,
                "title" => $item->title,
                "costco_number" => $item->costco_number,
                "regular_price" => "$" . $item->regular_price,
                "s_h" => "$" . $item->s_h,
                "coupon" => "$" . $item->coupon,
                "final_price" => "$" . $item->final_price,
                "coupon_start" => $item->coupon_start,
                "coupon_end" => $item->coupon_end,
                "out_of_stock" => $item->out_of_stock,
                "action" => $action
            );
        }
        echo json_encode($result);
    }

    public function clearPrice()
    {
        $this->get_model->update_item('prices', NULL, array(
            'regular_price' => '',
            's_h' => '',
            'coupon' => '',
            'final_price' => '',
            'coupon_start' => '',
            'coupon_end' => '',
            'out_of_stock' => ''
        ));
        echo json_encode(array('success' => true, 'msg' => ''));
    }

    public function makeCheck()
    {
        $id = $this->input->post('id');
        $id = implode(",", $id);
        $query = "select * from prices where id in ($id)";
        $qresult = $this->get_model->get_list($query);
        $item_list = array();
        foreach ($qresult as $item) {
            $item_list[] = (array)$item;
        }
        $msg = $this->check($item_list);
        if ($msg == '') {
            echo json_encode(array('success' => true, 'msg' => ''));
        } else {
            echo json_encode(array('success' => false, 'msg' => $msg));
        }
    }

    private function check($item_list)
    {
        error_reporting(E_ERROR);

        require_once("Curl.class.php");
        $curl = new Curl();
        $msg = '';
        foreach ($item_list as $order_item) {
            $id = $order_item['id'];
            $item_number = $order_item['costco_number'];

            $item_number_list = explode('-', $item_number);
            $item_number = $item_number_list[0];

            do {
                $product_url = 'http://m.costco.com/.product.' . $item_number . '.html';
                $content = $curl->get($product_url);

                $price_data = array(
                    'regular_price' => '',
                    's_h' => '',
                    'coupon' => '',
                    'coupon_start' => '',
                    'coupon_end' => '',
                    'final_price' => '',
                    'out_of_stock' => 'ERROR'
                );

                if (strpos($content, 'This product is out of stock and cannot be added to your cart at this time.')) {
                    $price_data['out_of_stock'] = 'Out of stock';
                    break;
                }
                if (strpos($content, 'title="Out of Stock"')) {
                    $price_data['out_of_stock'] = 'Out of stock';
                    break;
                }

                preg_match_all('/var\s+(products)\s*=\s*(["\']?)(.*?)\2];/i', preg_replace("/\r|\n/", "", $content), $matches);
                if (!isset($matches[3]) || !isset($matches[3][0])) {
                    $price_data['out_of_stock'] = 'ERROR';
                    break;
                }
                $string = preg_replace('/\s+/', '', $matches[3][0]);

                preg_match_all('/"options":\[([0-9",]*)\]/', $string, $options_list);
                $options_list = $options_list[1];
                foreach ($options_list as $idx => $option) {
                    $options_list[$idx] = explode(',', str_replace('"', '', $option));
                }

                preg_match_all('/"catentry":"([^"]*)"/', $string, $catentry_list);
                $catentry_list = $catentry_list[1];

                $catentry = FALSE;
                if (count($item_number_list) == 1) {
                    $catentry = $catentry_list[0];
                }
                if (count($item_number_list) == 2) {
                    foreach ($options_list as $idx => $options) {
                        if ($options[0] == trim($item_number_list[1])) {
                            $catentry = $catentry_list[$idx];
                            break;
                        }
                    }
                }
                if (count($item_number_list) == 3) {
                    foreach ($options_list as $idx => $options) {
                        if ($options[0] == trim($item_number_list[1]) && $options[1] == trim($item_number_list[2])) {
                            $catentry = $catentry_list[$idx];
                            break;
                        }
                    }
                }
                if (!$catentry) {
                    $price_data['out_of_stock'] = 'Out of stock';
                    break;
                }

                $dom = new DOMDocument();
                $dom->loadHTML($content);
                $xpath = new DOMXPath($dom);
                $result = $xpath->query("//div[contains(@class, 'online-price') and @data-catentry='$catentry']");
                if ($result->length == 0) {
                    $price_data['out_of_stock'] = 'Out of stock';
                    break;
                }
                $online_price = floatval(decode($result->item(0)->getAttribute('data-opvalue')));

                $coupon = "0.00";
                $result = $xpath->query("//div[contains(@class, 'disc') and @data-catentry='$catentry']");
                if ($result->length == 1) {
                    $coupon = decode($result->item(0)->getAttribute('data-disc'));
                }

                preg_match_all('/Shipping (&|&amp;) Handling: \$([0-9]+\.[0-9]+)[*]*/', $content, $shipping);
                $s_h = "0.00";
                if (isset($shipping[2], $shipping[2][0])) {
                    $s_h = $shipping[2][0];
                }

                preg_match_all('/\$([0-9]+) manufacturer(\’|\')s discount[*]* is valid ([^.]*) through ([^.]*)/', $content, $coupon_list);
                $coupon_start = "";
                $coupon_end = "";
                if (isset($coupon_list[1], $coupon_list[1][0], $coupon_list[3], $coupon_list[3][0], $coupon_list[4], $coupon_list[4][0])) {
                    $coupon_start = $coupon_list[3][0];
                    $coupon_end = $coupon_list[4][0];
                }


                $final_price = $online_price - floatval($coupon) + floatval($s_h);

                $price_data['regular_price'] = $online_price;
                $price_data['coupon'] = $coupon;
                $price_data['s_h'] = $s_h;
                $price_data['coupon_start'] = $coupon_start;
                $price_data['coupon_end'] = $coupon_end;
                $price_data['final_price'] = $final_price;
                $price_data['out_of_stock'] = '';
            } while (false);
            $this->get_model->update_item('prices', $id, $price_data);
            sleep(1);
        }
        $curl->close();
        return $msg;
    }

    public function delPrice()
    {
        $id = $this->input->post('id');
        $id = implode(",", $id);
        $query = "delete from prices where id in ($id)";
        $this->get_model->exec_query($query);
        echo json_encode(true);
    }

    public function exportPriceList()
    {
        include 'Classes/PHPExcel/IOFactory.php';
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'ASIN');
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, 'Title');
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(70);
        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, 'Costco Number');
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, 'Regular Price');
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, 'S&H');
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, 'Coupon');
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, 'Final Price');
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, 'Coupon Start');
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, 'Coupon End');
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, 'Out of Stock');
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

        $query = "select * from prices";
        $qresult = $this->get_model->get_list($query);
        foreach ($qresult as $item) {
            $row = (array)$item;
            $rowCount++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $row["asin"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $row["title"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $row["costco_number"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $row["regular_price"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $row["s_h"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $row["coupon"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $row["final_price"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $row["coupon_start"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $row["coupon_end"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $row["out_of_stock"]);
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $file_path = 'temp/' . date('Y-m-d') . "-" . uniqid() . "-price.xlsx";
        $objWriter->save($file_path);
        $result = true;
        $msg = $file_path;
        echo json_encode(array('success' => $result, 'msg' => $msg));
    }

    public function getGmailTrackList()
    {
        $result = array();
        $draw = $this->input->post("draw");
        $start = $this->input->post("start");
        $length = $this->input->post("length");
        $search = $this->input->post("search");

        $kind = $this->input->post("kind");
        $start_date = $this->input->post("start_date");
        $end_date = $this->input->post("end_date");

        $query = "select * from tracks where 1";
        if ($kind != 0) {
            $query .= " and kind='$kind'";
        }
        if ($start_date != '' && $end_date != '') {
            $start_time = $this->getTimeStamp($start_date);
            $end_time = $this->getTimeStamp($end_date, TRUE, FALSE);

            $query .= " and $start_time<=received and received<=$end_time";
        }
        $total = $this->get_model->get_count($query);
        if (strlen($search["value"]) > 0) {
            $query .= " and order_id='" . $search["value"] . "'";
        }
        $filtered = $this->get_model->get_count($query);
        $query .= " order by received desc";
        if ($length != -1)
            $query .= " limit $start, $length";
        $qresult = $this->get_model->get_list($query);
        foreach ($qresult as $item) {
            $result[] = array(
                'id' => $item->id,
                'gmail' => $item->gmail,
                'order_id' => $item->order_id,
                'kind' => $this->gmail_track_kind[$item->kind],
                'received' => date('Y-m-d H:i', $item->received)
            );
        }

        echo json_encode(array(
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $filtered,
            "data" => $result
        ));
    }

    protected function getTimeStamp($datestr, $dateonly = TRUE, $datestart = TRUE)
    {
        if ($dateonly) {
            if ($datestart)
                $dtime = DateTime::createFromFormat('Y-m-d H:i:s', $datestr . " 00:00:00");
            else
                $dtime = DateTime::createFromFormat('Y-m-d H:i:s', $datestr . " 23:59:59");
        } else {
            $dtime = DateTime::createFromFormat('Y-m-d H:i:s', $datestr);
        }
        if (!$dtime)
            return 0;
        return $dtime->getTimestamp();
    }

    public function exportGmailTracks()
    {
        include 'Classes/PHPExcel/IOFactory.php';
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, 'Order No');
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, 'Kind');
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, 'Mail Address');
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, 'Received Time');
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);

        $kind = $this->input->post("kind");
        $start_date = $this->input->post("start_date");
        $end_date = $this->input->post("end_date");

        $query = "select * from tracks where 1";
        if ($kind != 0) {
            $query .= " and kind='$kind'";
        }
        if ($start_date != '' && $end_date != '') {
            $start_time = $this->getTimeStamp($start_date);
            $end_time = $this->getTimeStamp($end_date, TRUE, FALSE);

            $query .= " and $start_time<=received and received<=$end_time";
        }

        $qresult = $this->get_model->get_list($query);
        foreach ($qresult as $item) {
            $rowCount++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $item->order_id);
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $this->gmail_track_kind[$item->kind]);
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $item->gmail);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, date('Y-m-d H:i', $item->received));
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $file_path = 'temp/' . date('Y-m-d') . "-" . uniqid() . "-notice.xlsx";
        $objWriter->save($file_path);
        $result = true;
        $msg = $file_path;
        echo json_encode(array('success' => $result, 'msg' => $msg));
    }

    public function trackGmail()
    {
        error_reporting(0);
        $start_date = $this->input->post('start_date');
        $start_date = date('j F Y', strtotime($start_date));
        $end_date = $this->input->post('end_date');
        $end_date = date('j F Y', strtotime($end_date . ' +1 day'));

        $msg = '';
        $refund_count = 0;
        $shipping_count = 0;
        $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
        $gmail_list = $this->config->item('gmail_list');
        foreach ($gmail_list as $gmail_data) {
            $username = $gmail_data['gmail'];
            $password = $gmail_data['password'];
            $inbox = imap_open($hostname, $username, $password);
            if (!$inbox) {
                $msg .= 'Cannot connect to ' . $username . ': ' . imap_last_error() . "\n";
                imap_close($inbox);
                continue;
            }

            $emails = imap_search($inbox, 'FROM "order-refund@costco.com" SINCE "' . $start_date . '" BEFORE "' . $end_date . '"');

            if ($emails) {
                rsort($emails);
                foreach ($emails as $email_number) {
                    $overview = imap_fetch_overview($inbox, $email_number, 0);
                    $subject = $overview[0]->subject;
                    preg_match('/Costco.com Refund Notification ([0-9]*)/', $subject, $match);
                    if (empty($match)) {
                        continue;
                    }
                    $order_id = $match[1];
                    $received = strtotime($overview[0]->date);

                    $message = imap_qprint(imap_fetchbody($inbox, $email_number, 1))
                        . imap_qprint(imap_fetchbody($inbox, $email_number, 2));
                    $kind = 0;

                    if (strpos($message, $this->gmail_track_kind['1'])) {
                        $kind = 1;
                    } elseif (strpos($message, $this->gmail_track_kind['2'])) {
                        $kind = 2;
                    } elseif (strpos($message, $this->gmail_track_kind['3'])) {
                        $kind = 3;
                    }

                    if (!$kind) {
                        continue;
                    }

                    $query = "select * from tracks where order_id='$order_id' AND kind='$kind'";
                    if ($this->get_model->get_count($query) > 0) {
                        continue;
                    }
                    $data = array(
                        'gmail' => $username,
                        'order_id' => $order_id,
                        'kind' => $kind,
                        'received' => $received
                    );
                    $this->get_model->insert_item('tracks', $data);
                    $refund_count++;
                }
            }


            $emails = imap_search($inbox, 'FROM "orderstatus@costco.com" SINCE "' . $start_date . '" BEFORE "' . $end_date . '"');
            if ($emails) {
                rsort($emails);
                foreach ($emails as $email_number) {
                    $overview = imap_fetch_overview($inbox, $email_number, 0);
                    $subject = $overview[0]->subject;
                    preg_match('/Your Costco.com order has been shipped ([0-9]*)/', $subject, $match);
                    if (empty($match)) {
                        continue;
                    }
                    $order_id = $match[1];
                    $received = strtotime($overview[0]->date);
                    $kind = 4;
                    $query = "select * from tracks where order_id='$order_id' AND kind='$kind'";
                    if ($this->get_model->get_count($query) > 0) {
                        continue;
                    }
                    $data = array(
                        'gmail' => $username,
                        'order_id' => $order_id,
                        'kind' => $kind,
                        'received' => $received
                    );
                    $this->get_model->insert_item('tracks', $data);
                    $shipping_count++;
                }
            }

            imap_close($inbox);
        }

        $result = $msg == '';
        if ($result) {
            $msg = "Refund Notification: " . $refund_count . "\n" . "Shipping Notification: " . $shipping_count;
        }
        echo json_encode(array('success' => $result, 'msg' => $msg));
    }

    public function delGamilTrack()
    {
        $id = $this->input->post('id');

        $query = "delete from tracks";
        if ($id) {
            $id = implode(",", $id);
            $query .= " where id in ($id)";
        }
        $this->get_model->exec_query($query);
        echo json_encode(true);
    }

    public function test()
    {
        /* Create gmail connection */
        $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
        $username = 'mulikadf@gmail.com';
        $password = 'asdQWE123!@#';
        $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to Gmail: ' . imap_last_error());

        /* Fetch emails */
        $emails = imap_search($inbox, 'FROM "gtscostco1@gmail.com" SINCE "2017-03-01"');
        /* If emails are returned, cycle through each... */
        if ($emails) {
            $output = '';

            /* Make the newest emails on top */
            rsort($emails);

            /* For each email... */
            foreach ($emails as $email_number) {
                //$headerInfo = imap_headerinfo($inbox,$email_number);
                //$structure = imap_fetchstructure($inbox, $email_number);

                /* get information specific to this email */
                $overview = imap_fetch_overview($inbox, $email_number, 0);
                $subject = $overview[0]->subject;

                var_dump(date('Y-m-d H:i:s', strtotime($overview[0]->date)));

                //$output .= 'Subject: '.$overview[0]->subject.'<br />';
                //$output .= 'Body: '.$message.'<br />';
                //$output .= 'From: '.$overview[0]->from.'<br />';
                //$output .= 'Date: '.$overview[0]->date.'<br />';
                //$output .= 'CC: '.$headerInfo->ccaddress.'<br />';
            }

            echo $output;
        }

        /* close the connection */
        imap_close($inbox);
    }

    private function order2($item_list)
    {
        error_reporting(E_ERROR);

        require_once("Curl.class.php");
        $curl = new Curl();

        $username = $this->config->item('username');
        $password = $this->config->item('password');
        $email = $this->config->item('email');
        $cvv = $this->config->item('cvv');
        $expire_month = $this->config->item('expire_month');
        $expire_year = $this->config->item('expire_year');
        $membershipNum = $this->config->item('membershipNum');
        $selfAddressId = $this->config->item('selfAddressId');
        $shipModeId_1 = $this->config->item('shipModeId_1');
        $shipModeId_2 = $this->config->item('shipModeId_2');


        $msg = '';
        do {
            // $curl->get('https://m.costco.com/Logoff');
            //$content = $curl->get('https://m.costco.com/LogonForm?URL=LogonForm&_pjax=true');//
            // $content = $curl->get('https://m.costco.com/LogonForm');
            // $dom = new DOMDocument();
            // $dom->loadHTML($content);
            // $xpath = new DOMXPath($dom);
            // $results = $xpath->query("//form[@id='LogonForm']");
            //$results = $xpath->query("//form[@id='login']");//
            //    if ($results->length == 0) {
            //       $msg = "can`t load login page111";
            //      break;
            // }
            // $login_data = array();
            // foreach ($results as $item) {
            //    $result = $xpath->query(".//input[@type='hidden']", $item);
            //   foreach ($result as $input) {
            //       $login_data[$input->getAttribute('name')] = $input->getAttribute('value');
            //   }
            //   $login_data['logonId'] = $username;
            //   $login_data['logonPassword'] = $password;
            //   $login_data['option1'] = 'on';
            //  break;
            // }
            //$content = $curl->post('https://m.costco.com/LogonForm', $login_data);//
            //$content = $curl->post('https://m.costco.com/Logon', $login_data);
            //$response = $curl->getResponse();
            //if (strpos($response['url'], 'catalogId')) {
            //    $msg = "login failed111";
            //   break;
            // }
            $session = $this->init_costco();
            foreach ($item_list as $order_item) {

                $id = $order_item['id'];
                $item_number = $order_item['product_number'];
                $quantity = $order_item['quantity_purchased'];
                $ship_service = $order_item['ship_service'];
                $name = $order_item['recipient_name'];
                $address1 = $order_item['ship_address_1'];
                $address2 = $order_item['ship_address_2'];
                $city = $order_item['ship_city'];
                $state = $order_item['ship_state'];
                $zip = $order_item['ship_postal_code'];
                $number = $order_item['buyer_phone_number'];

                $item_number_list = explode('-', $item_number);
                $item_number = $item_number_list[0];


                $content = $this->curl_get($session, 'https://m.costco.com/CheckoutCartView?orderId=.&_pjax=true');
                $dom = new DOMDocument();
                $dom->loadHTML($content);
                $xpath = new DOMXPath($dom);
                $results = $xpath->query("//div[@class='remove-link hidden-xs hidden-sm']");
                foreach ($results as $item) {
                    $result = $xpath->query(".//a", $item);
                    foreach ($result as $input) {
                        $url = $input->getAttribute('href');
                        $this->curl_get($session, 'https:' . $url);
                    }
                }

                $order_result = array('order_msg' => 'Unknown Error');
                do {
                    $product_url = 'http://m.costco.com/.product.' . $item_number . '.html';
                    $content = $this->curl_get($session, $product_url);
                    $dom = new DOMDocument();
                    $dom->loadHTML($content);
                    $xpath = new DOMXPath($dom);
                    $results = $xpath->query("//form[@id='ProductForm']");
                    if ($results->length == 0) {
                        $order_result['order_msg'] = "doesn`t exist product";
                        break;
                    }

                    preg_match_all('/var\s+(products)\s*=\s*(["\']?)(.*?)\2];/i', preg_replace("/\r|\n/", "", $content), $matches);
                    $string = preg_replace('/\s+/', '', $matches[3][0]);
                    preg_match_all('/"catentry":"([^"]*)"/', $string, $catentry_list);
                    $catentry_list = $catentry_list[1];
                    preg_match_all('/"options":\[([0-9",]*)\]/', $string, $options_list);
                    $options_list = $options_list[1];
                    foreach ($options_list as $idx => $option) {
                        $options_list[$idx] = explode(',', str_replace('"', '', $option));
                    }

                    $prop_data = array();
                    foreach ($results as $item) {
                        $result = $xpath->query(".//input[@type='hidden']", $item);
                        foreach ($result as $input) {
                            $prop_data[$input->getAttribute('name')] = $input->getAttribute('value');
                        }
                        if (count($item_number_list) == 2) {
                            foreach ($options_list as $idx => $options) {
                                if ($options[0] == $item_number_list[1]) {
                                    $prop_data['catEntryId'] = $catentry_list[$idx];
                                    $prop_data['productOption00'] = $options[0];
                                    break;
                                }
                            }
                        }
                        if (count($item_number_list) == 3) {
                            foreach ($options_list as $idx => $options) {
                                if ($options[0] == $item_number_list[1] && $options[1] == $item_number_list[2]) {
                                    $prop_data['catEntryId'] = $catentry_list[$idx];
                                    $prop_data['productOption00'] = $options[0];
                                    $prop_data['productOption01'] = $options[1];
                                    break;
                                }
                            }
                        }
                        $prop_data['quantity'] = $quantity;
                        $prop_data['ajaxFlag'] = true;
                        break;
                    }

                    if ((count($item_number_list) == 2 && !isset($prop_data['productOption00'])) || (count($item_number_list) == 3 && !isset($prop_data['productOption01']))
                    ) {
                        $order_result['order_msg'] = "product select option is not existed";
                        break;
                    }

                    $content = $this->curl_post($session, 'http://m.costco.com/AjaxManageShoppingCartCmd', $prop_data);
                    $content = strip_tags($content);
                    $order_data = json_decode($content, true);
                    if (!$order_data || !isset($order_data['orderErrMsgObj']) || count($order_data['orderErrMsgObj']) > 0) {
                        $order_result['order_msg'] = "failed adding to cart";
                        break;
                    }

                    $content = $this->curl_get($session, 'https://m.costco.com/CheckoutCartView?orderId=.&_pjax=true');
                    $dom = new DOMDocument();
                    $dom->loadHTML($content);
                    $xpath = new DOMXPath($dom);
                    $results = $xpath->query("//form[@id='ShopCartForm']");
                    if ($results->length == 0) {
                        $order_result['order_msg'] = "doesn`t exist cart";
                        break;
                    }
                    $cart_data = array();
                    foreach ($results as $item) {
                        $result = $xpath->query(".//input[@type='hidden']", $item);
                        foreach ($result as $input) {
                            $cart_data[$input->getAttribute('name')] = $input->getAttribute('value');
                        }
                        $cart_data['quantity_1'] = $quantity;
                        break;
                    }
                    $orderId = $cart_data['orderId'];

                    $page = $session->getPage();
                    sleep(5);
                    $CheckoutLink = $page->find('named', array('id', "shopCartCheckoutSubmitButton"));
                    $CheckoutLink->click();
                    sleep(2);
                    $u = $session->getCurrentUrl();
                    if (strstr($u, "Logon")) {

                        $this->retry_login($session);
                    }
                    //echo "Check What do do from this step";
                    //break;
                    //---------- Proceed of checkout ------------//
                    //Blocked it here
                    //$content = $this->curl_post($session,'https://m.costco.com/ManageShoppingCartCmd?actionType=checkout', $cart_data);
                    //---------- Registering of address info ------------//
                    $name_split = explode(' ', $name);
                    $fName = $name_split[0];
                    $lName = trim(str_replace($fName, '', $name));
                    if ($lName == '')
                        $lName = 'a';
                    $shipping_data = array(
                        'id' => '',
                        'fName' => $fName,
                        'lName' => $lName,
                        'company' => '',
                        'country' => 'US',
                        'line1' => $address1,
                        'line2' => $address2,
                        'zip' => $zip,
                        'city' => $city,
                        'state' => $state,
                        'pNumber' => $number,
                        'nickname' => $number . '-' . rand(100, 999),
                        'email' => $email,
                        'saveAddress' => 'off',
                        'setDefault' => 'off',
                        'addressType2' => ''
                    );
                    $address_data = array(
                        'authToken' => $cart_data['authToken'],
                        'formdata' => urlencode(json_encode($shipping_data))
                    );

                    $content = $this->curl_post($session, 'https://m.costco.com/AjaxAddressAddCmd?addressType=S&applyAddressToOrder=false&applyAddressToOrderItems=true', $address_data);
                    $content = strip_tags($content);
                    $address_result = json_decode($content, true);
                    if (!$address_result || !isset($address_result[0]) || $address_result[0]["success"] != 'true') {
                        $order_result['order_msg'] = "address error";
                        break;
                    }

                    //---------- Setting of shipping address ------------//
                    /*
                      $data = array(
                      'storeId' => $login_data['storeId'],
                      'langId' => $login_data['langId'],
                      'catalogId' => $login_data['catalogId'],
                      'action' => 'SingleShipping',
                      'addressId' => $address_result[1]["addressId"]
                      );

                      $content = $curl->post('https://m.costco.com/CostcoSelectShippingCmd?_pjax=true', $data);

                      //---------- Getting of delivery form data ------------//
                      $dom = new DOMDocument();
                      $dom->loadHTML($content);
                      $xpath = new DOMXPath($dom);
                      $results = $xpath->query("//form[@id='CheckoutPaymentForm']");
                      if ($results->length == 0) {
                      $order_result['order_msg'] = "doesn`t exist delivery option";
                      break;
                      }

                      $delivery_data = array();
                      foreach ($results as $item) {
                      $result = $xpath->query(".//input[@type='hidden']", $item);
                      foreach ($result as $input) {
                      $delivery_data[$input->getAttribute('name')] = $input->getAttribute('value');
                      }
                      if ($ship_service == 0)
                      $delivery_data['shipModeId_1'] = $shipModeId_1;
                      else
                      $delivery_data['shipModeId_1'] = $shipModeId_2;
                      break;
                      }
                      $results = $xpath->query("//select[@id='shipModeId_1']");
                      if ($results->length != 0) {
                      $result = $xpath->query(".//option", $results->item(0));
                      if ($result->length != 0) {
                      if ($ship_service == 0)
                      $delivery_data['shipModeId_1'] = $result->item(0)->getAttribute('value');
                      else
                      $delivery_data['shipModeId_1'] = $result->item(1)->getAttribute('value');
                      }
                      }
                      $etc = "";
                      $results = $xpath->query("//div[@class='number-status']");
                      if ($results->length > 0) {
                      $etc_status = $results->item(0)->textContent;
                      $start_pos = strpos($etc_status, 'This product');
                      if ($start_pos)
                      $etc = substr($etc_status, $start_pos);
                      }

                     */

                    //---------- Proceed of delivering option ------------//
                    //$content = $curl->post('https://m.costco.com/CheckoutPaymentView?', $delivery_data);
                    //$content = $this->curl_post($session,'https://m.costco.com/CostcoShipinfoUpdateCmd?_pjax=true', $delivery_data);
                    //---------- Getting of Payment form data ------------//
// NEED TO GET THE CHECKOUT PAYMENT VIEW PAGE CONTENT
                    $content = $this->curl_get($session, 'https://m.costco.com/CheckoutPaymentView');
                    $dom = new DOMDocument();
                    $dom->loadHTML($content);
                    $xpath = new DOMXPath($dom);

// ul and li tags are no longer there.  It has been changed over to using dl and dd. See below...
//                    $ul_results = $xpath->query("//ul[@class='order-table']");
//                    $results = $xpath->query(".//li", $ul_results->item(0));
//                    $shipping_item = $xpath->query(".//span[@class='right']", $results->item($results->length - 4));
//                    $shipping_charge = $shipping_item->item(0)->textContent;
//                    $tax_item = $xpath->query(".//span[@class='right']", $results->item($results->length - 3));
//                    $tax = $tax_item->item(0)->textContent;
//                    $total_item = $xpath->query(".//span[@class='right']", $results->item($results->length - 1));
//                    $order_total = $total_item->item(0)->textContent;
//BEGIN DL CONTAINERS
                    //Order Summary container
                    $dl_results = $xpath->query("//dl[@class='dl-horizontal']");

                    //go inside the first dl container to retrieve the shipping and tax amounts
                    $results = $xpath->query(".//dd", $dl_results->item(0));

                    $shipping_item = trim($results->item(1)->textContent);
                    $shipping_charge = str_replace('$', '', $shipping_item);

                    $tax_item = trim($results->item(2)->textContent);
                    $tax = str_replace('$', '', $tax_item);

                    //go inside the second dl container to retrieve the total amount
                    $results = $xpath->query(".//dd", $dl_results->item(1));

                    $total_item = trim($results->item(0)->textContent);
                    $order_total = str_replace('$', '', $total_item);
//END DL CONTAINERS
                    $results = $xpath->query("//form[@id='CheckoutPaymentForm']");
                    if ($results->length == 0) {
                        $order_result['order_msg'] = "doesn`t exist payment option";
                        break;
                    }

                    $payment_data = array();
                    foreach ($results as $item) {
                        $result = $xpath->query(".//input", $item);
                        foreach ($result as $input) {
                            $payment_data[$input->getAttribute('name')] = $input->getAttribute('value');
                        }
                        $payment_data['expire_month'] = $expire_month;
                        $payment_data['expire_year'] = $expire_year;
                        $payment_data['cc_cvc'] = $cvv;
                        $payment_data['membershipNum'] = $membershipNum;
                        $payment_data['selfAddressId'] = $selfAddressId;
                        $payment_data['billHideenInput'] = $selfAddressId;
                        $payment_data['billAddrId'] = $selfAddressId;
                        $payment_data['orderItemsCount'] = '1';
                        unset($payment_data['']);
                        break;
                    }

                    $content = $this->curl_post($session, 'https://m.costco.com/CostcoBillingPayment?_pjax=true', $payment_data);

                    $dom = new DOMDocument();
                    $dom->loadHTML($content);
                    $xpath = new DOMXPath($dom);
                    $results = $xpath->query("//form[@id='CheckoutReviewForm']");
                    if ($results->length == 0) {
                        $order_result['order_msg'] = "doesn`t exist verify form";
                        break;
                    }
                    $verify_data = array();
                    foreach ($results as $item) {
                        $result = $xpath->query(".//input[@type='hidden']", $item);
                        foreach ($result as $input) {
                            $verify_data[$input->getAttribute('name')] = $input->getAttribute('value');
                        }
                        break;
                    }
                    // Best Guess we are breaking up somewhere here

                    $content = $this->curl_post($session, 'https://m.costco.com/CostcoOrderProcess', $verify_data);
                    if ($etc != "") {
                        $dom = new DOMDocument();
                        $dom->loadHTML($content);
                        $xpath = new DOMXPath($dom);
                        $results = $xpath->query("//form[@id='CheckoutReviewForm']");
                        if ($results->length > 0) {
                            $verify_data = array();
                            foreach ($results as $item) {
                                $result = $xpath->query(".//input[@type='hidden']", $item);
                                foreach ($result as $input) {
                                    $verify_data[$input->getAttribute('name')] = $input->getAttribute('value');
                                }
                                break;
                            }
                            $content = $this->curl_post($session, 'https://m.costco.com/CostcoOrderProcess', $verify_data);
                        }
                    }
                    if (strstr($content, "order total has changed")) {

                        //echo "Got it";
                        //cc_cvc

                        $SignInForm = $page->find('named', array('id', "CheckoutPaymentForm"));

                        if (null === $SignInForm) {

                            echo "Not Found";
                        } else {

                            //echo " Form Found <br/>";
                            $cvcc = $page->find('named', array('id', "cc_cvc"));

                            $cvcc->setValue($cvv);

                            //echo " Login and Password Added<br/>";
                            $SignInForm->submit();
                            //echo $session->getCurrentUrl();
                            //echo " Form Submit <br/>";
                            $page = $session->getPage();
                            //var_dump($page);
                            //var_dump($page->getHtml());
                        }
                    }


                    $dom = new DOMDocument();
                    $dom->loadHTML($content);
                    $xpath = new DOMXPath($dom);
                    $results = $xpath->query("//form[@id='payment']");
                    if ($results->length > 0) {
                        $content = $this->curl_get($session, 'https://m.costco.com/CheckoutCartView?orderId=.&_pjax=true');
                        $dom = new DOMDocument();
                        $dom->loadHTML($content);
                        $xpath = new DOMXPath($dom);
                        $results = $xpath->query("//div[@class='remove-link hidden-xs hidden-sm']");
                        foreach ($results as $item) {
                            $result = $xpath->query(".//a", $item);
                            foreach ($result as $input) {
                                $url = $input->getAttribute('href');
                                $this->curl_get($session, 'https:' . $url);
                            }
                        }
                        break;
                    }

                    $order_result['order_msg'] = '';
                    $order_result['etc'] = $etc;
                    $order_result['costco_order_id'] = $orderId;
                    $order_result['shipping_charge'] = $shipping_charge;
                    $order_result['tax'] = $tax;
                    $order_result['total'] = $order_total;
                    $order_result['created'] = date('Y-m-d H:i:s');
                } while (false);
                $this->get_model->update_item('orders', $id, $order_result);
                sleep(5);
                // $session->stop();
            }
        } while (false);
        // $curl->get('https://m.costco.com/Logoff');
        // $curl->close();
        //You can Comment this line for debug this actully closes the browser window
        // $this->get_model->exec_query("TRUNCATE TABLE `requests`");
        return $msg;
    }

}
