<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Account_payables extends CORE_Controller
{

    function __construct() {
        parent::__construct('');
        $this->validate_session();

        $this->load->model(
            array(
                'Suppliers_model',
                'Account_title_model',
                'Payment_method_model',
                'Journal_info_model',
                'Journal_account_model',
                'Tax_types_model',
                'Delivery_invoice_model',
                'Departments_model',
                'Accounting_period_model'
            )
        );

    }

    public function index() {

        //default resources of the active view
        $data['_def_css_files'] = $this->load->view('template/assets/css_files', '', TRUE);
        $data['_def_js_files'] = $this->load->view('template/assets/js_files', '', TRUE);
        $data['_switcher_settings'] = $this->load->view('template/elements/switcher', '', TRUE);
        $data['_side_bar_navigation'] = $this->load->view('template/elements/side_bar_navigation', '', TRUE);
        $data['_top_navigation'] = $this->load->view('template/elements/top_navigation', '', TRUE);

        $data['suppliers']=$this->Suppliers_model->get_list('is_active=TRUE AND is_deleted=FALSE');
        $data['departments']=$this->Departments_model->get_list('is_active=TRUE AND is_deleted=FALSE');
        $data['accounts']=$this->Account_title_model->get_list('is_active=TRUE AND is_deleted=FALSE');
        $data['methods']=$this->Payment_method_model->get_list('is_active=TRUE AND is_deleted=FALSE');
        $data['tax_types']=$this->Tax_types_model->get_list('is_deleted=FALSE');

        $data['title'] = 'Accounts Payable';
        (in_array('1-3',$this->session->user_rights)? 
        $this->load->view('accounts_payable_view', $data)
        :redirect(base_url('dashboard')));
    }


    public function transaction($txn=null){
        switch($txn){
            case 'list':
                $m_journal=$this->Journal_info_model;
                $tsd = date('Y-m-d',strtotime($this->input->get('tsd')));
                $ted = date('Y-m-d',strtotime($this->input->get('ted')));
                $additional = " AND DATE(journal_info.date_txn) BETWEEN '$tsd' AND '$ted'";
                $response['data']=$this->get_response_rows(null,$additional);
                echo json_encode($response);
                break;
            case 'get-entries':
                $journal_id=$this->input->get('id');
                $m_accounts=$this->Account_title_model;
                $m_journal_accounts=$this->Journal_account_model;

                $data['accounts']=$m_accounts->get_list();
                $data['entries']=$m_journal_accounts->get_list('journal_accounts.journal_id='.$journal_id);

                $this->load->view('template/journal_entries', $data);
                break;
            case 'create' :
                $m_journal=$this->Journal_info_model;
                $m_journal_accounts=$this->Journal_account_model;

                //validate if still in valid range
                $valid_range=$this->Accounting_period_model->get_list("'".date('Y-m-d',strtotime($this->input->post('date_txn',TRUE)))."'<=period_end");
                if(count($valid_range)>0){
                    $response['stat']='error';
                    $response['title']='<b>Accounting Period is Closed!</b>';
                    $response['msg']='Please make sure transaction date is valid!<br />';
                    die(json_encode($response));
                }

                $m_journal->supplier_id=$this->input->post('supplier_id',TRUE);
                $m_journal->department_id=$this->input->post('department_id',TRUE);
                $m_journal->remarks=$this->input->post('remarks',TRUE);
                $m_journal->date_txn=date('Y-m-d',strtotime($this->input->post('date_txn',TRUE)));
                $m_journal->book_type='PJE';

                //for audit details
                $m_journal->set('date_created','NOW()');
                $m_journal->created_by_user=$this->session->user_id;
                $m_journal->save();

                $journal_id=$m_journal->last_insert_id();
                $accounts=$this->input->post('accounts',TRUE);
                $memos=$this->input->post('memo',TRUE);
                $dr_amounts=$this->input->post('dr_amount',TRUE);
                $cr_amounts=$this->input->post('cr_amount',TRUE);

                for($i=0;$i<=count($accounts)-1;$i++){
                    $m_journal_accounts->journal_id=$journal_id;
                    $m_journal_accounts->account_id=$accounts[$i];
                    $m_journal_accounts->memo=$memos[$i];
                    $m_journal_accounts->dr_amount=$this->get_numeric_value($dr_amounts[$i]);
                    $m_journal_accounts->cr_amount=$this->get_numeric_value($cr_amounts[$i]);
                    $m_journal_accounts->save();
                }


                //update transaction number base on formatted last insert id
                $m_journal->txn_no='TXN-'.date('Ymd').'-'.$journal_id;
                $m_journal->modify($journal_id);


                //if dr invoice is available, purchase invoice is recorded as journal
                $dr_invoice_id=$this->input->post('dr_invoice_id',TRUE);
                if($dr_invoice_id!=null){
                    $m_purchase_invoice=$this->Delivery_invoice_model;
                    $m_purchase_invoice->journal_id=$journal_id;
                    $m_purchase_invoice->is_journal_posted=TRUE;
                    $m_purchase_invoice->modify($dr_invoice_id);
                }


                $response['stat']='success';
                $response['title']='Success!';
                $response['msg']='Journal successfully posted';
                $response['row_added']=$this->get_response_rows($journal_id);
                echo json_encode($response);
                break;
            case 'update':
                $journal_id=$this->input->get('id');
                $m_journal=$this->Journal_info_model;
                $m_journal_accounts=$this->Journal_account_model;

                //validate if this transaction is not yet closed
                $not_closed=$m_journal->get_list('accounting_period_id>0 AND journal_id='.$journal_id);
                if(count($not_closed)>0){
                    $response['stat']='error';
                    $response['title']='<b>Journal is Locked!</b>';
                    $response['msg']='Sorry, you cannot update journal that is already closed!<br />';
                    die(json_encode($response));
                }

                //validate if still in valid range
                $valid_range=$this->Accounting_period_model->get_list("'".date('Y-m-d',strtotime($this->input->post('date_txn',TRUE)))."'<=period_end");
                if(count($valid_range)>0){
                    $response['stat']='error';
                    $response['title']='<b>Accounting Period is Closed!</b>';
                    $response['msg']='Please make sure transaction date is valid!<br />';
                    die(json_encode($response));
                }

                $m_journal->supplier_id=$this->input->post('supplier_id',TRUE);
                $m_journal->department_id=$this->input->post('department_id',TRUE);
                $m_journal->remarks=$this->input->post('remarks',TRUE);
                $m_journal->date_txn=date('Y-m-d',strtotime($this->input->post('date_txn',TRUE)));
                $m_journal->book_type='PJE';

                //for audit details
                $m_journal->set('date_modified','NOW()');
                $m_journal->modified_by_user=$this->session->user_id;
                $m_journal->modify($journal_id);


                $accounts=$this->input->post('accounts',TRUE);
                $memos=$this->input->post('memo',TRUE);
                $dr_amounts=$this->input->post('dr_amount',TRUE);
                $cr_amounts=$this->input->post('cr_amount',TRUE);

                $m_journal_accounts->delete_via_fk($journal_id);

                for($i=0;$i<=count($accounts)-1;$i++){
                    $m_journal_accounts->journal_id=$journal_id;
                    $m_journal_accounts->account_id=$accounts[$i];
                    $m_journal_accounts->memo=$memos[$i];
                    $m_journal_accounts->dr_amount=$this->get_numeric_value($dr_amounts[$i]);
                    $m_journal_accounts->cr_amount=$this->get_numeric_value($cr_amounts[$i]);
                    $m_journal_accounts->save();
                }


                $response['stat']='success';
                $response['title']='Success!';
                $response['msg']='Journal successfully updated';
                $response['row_updated']=$this->get_response_rows($journal_id);
                echo json_encode($response);
                break;
            //***************************************************************************************
            case 'cancel':
                $m_journal=$this->Journal_info_model;
                $journal_id=$this->input->post('journal_id',TRUE);

                //validate if this transaction is not yet closed
                $not_closed=$m_journal->get_list('accounting_period_id>0 AND journal_id='.$journal_id);
                if(count($not_closed)>0){
                    $response['stat']='error';
                    $response['title']='<b>Journal is Locked!</b>';
                    $response['msg']='Sorry, you cannot cancel journal that is already closed!<br />';
                    die(json_encode($response));
                }

                //mark Items as deleted
                $m_journal->set('date_cancelled','NOW()'); //treat NOW() as function and not string
                $m_journal->cancelled_by_user=$this->session->user_id;//user that cancelled the record
                $m_journal->set('is_active','NOT is_active');
                $m_journal->modify($journal_id);



                $response['title']='Cancelled!';
                $response['stat']='success';
                $response['msg']='Journal successfully cancelled.';
                $response['row_updated']=$this->get_response_rows($journal_id);

                echo json_encode($response);

                break;
        };
    }



    public function get_response_rows($criteria=null,$additional=null){
        $m_journal=$this->Journal_info_model;
        return $m_journal->get_list(

            "journal_info.is_deleted=FALSE AND journal_info.book_type='PJE'".($criteria==null?'':'  AND journal_info.journal_id='.$criteria)."".($additional==null?'':$additional),

            array(
                'journal_info.journal_id',
                'journal_info.txn_no',
                'DATE_FORMAT(journal_info.date_txn,"%m/%d/%Y")as date_txn',
                'journal_info.is_active',
                'journal_info.remarks',
                'journal_info.supplier_id',
                'journal_info.customer_id',
                'journal_info.department_id',
                'CONCAT(IFNULL(customers.customer_name,""),IFNULL(suppliers.supplier_name,""))as particular',
                'CONCAT_WS(" ",user_accounts.user_fname,user_accounts.user_lname)as posted_by'
            ),
            array(
                array('customers','customers.customer_id=journal_info.customer_id','left'),
                array('suppliers','suppliers.supplier_id=journal_info.supplier_id','left'),
                array('user_accounts','user_accounts.user_id=journal_info.created_by_user','left')
            )
        );
    }






}
