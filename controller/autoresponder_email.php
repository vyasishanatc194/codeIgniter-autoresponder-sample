<?php
/**
* A Autoresponder_email class for mark autoresponder mail :read, unsubscribe
*
* This class is for unsubscriber mail
*
* @version 1.0
* @author Ishan Vyas <vyasishanatc194@gmail.com>
* @project 
*/
class Autoresponder_email extends CI_Controller
{
	/**
		Contructor for controller.
		It checks user session and redirects user if not logged in
	*/
	function __construct(){
        parent::__construct();
		
		$this->load->model('Emailreport_Model');
    }
	
	/**
		Function unsubscribe to Mark autoresponder mail as unsubscribe 
		@param int id contain autoresponder id
		@param int scheduled_id contain autoresponder scheduled id
		@param int subscriber_id contain  subscriber id
	*/
	function unsubscribe($id=0,$scheduled_id=0,$subscriber_id=0){
		 
			
		$subscriber_id = str_replace('.html','',$subscriber_id);
		$arrSubscriber = $this->is_authorized->decodeSubscriber($subscriber_id);
		list($subscriber_id,$subscriber_email) = $arrSubscriber;	
		$subscriber_email = $this->is_authorized->webCompatibleString($subscriber_email);
		
		# Load subscriber model class which handles database interaction
		$this->load->model('newsletter/Subscriber_Model');
		# Load the user model which interact with database
		$this->load->model('UserModel');
		#Check scheduled_id should not be empty
		if($scheduled_id==0){
			# Load camapign model class which handles database interaction
			$this->load->model('newsletter/Autoresponder_Model');
			#Prepare array for where condition in an campign model
			$fetch_condiotions_array=array(
				'campaign_id'=>$id,
			);
			# Fetches campaign data from database
			$campaign_array=$this->Autoresponder_Model->get_autoresponder_data($fetch_condiotions_array);
			$user_id=$campaign_array[0]['campaign_created_by'];
			#Fetch user info
			$user=$this->UserModel->get_user_data(array('member_id'=>$user_id));
			#redirect to thanks msg
			redirect('newsletter/unsubscribe_mail/unsubscirbe_msg/'.$user[0]['rc_logo']);
		}else{
			# Load emailreport model class which handles database interaction
			$this->load->model('newsletter/Emailreport_Model');
			# Fetch condition
			$fetch_condiotions_array=array(	'autoresponder_scheduled_id'=>$scheduled_id,'email_track_subscriber_id '=>$subscriber_id,'res.subscriber_email'=>$subscriber_email);
			$email_report=$this->Emailreport_Model->get_autoresponder_emailreport_subscriber($fetch_condiotions_array);
			$email_id=$email_report[0]['subscriber_email'];# get email id
			$subscriber_created_by=$email_report[0]['subscriber_created_by'];# member id
			
			# check unsubscribe  mail for signup list
			$conditions_array=array(
				'autoresponder_scheduled_id'=>$scheduled_id,
				'email_track_subscriber_id '=>$subscriber_id
			);
			$email_report=$this->Emailreport_Model->update_autoresponder_emailreport(array('email_track_unsubscribes'=>1,'email_track_read'=>1),$conditions_array);
			//Unsubscribe contact
			$this->Subscriber_Model->update_subscriber(array('subscriber_status'=>0),array('subscriber_id'=>$subscriber_id));
			#Fetch user info
			$user=$this->UserModel->get_user_data(array('member_id'=>$subscriber_created_by));			
			#redirect to thanks msg
			$this->thanks_msg($user[0]['rc_logo']);
		}
	}
	/**
		Function thanks_msg to display thanks message to user
		@param int rc_logo contain redcapi logo status
	*/
	function thanks_msg($rc_logo=1){
		$msg= '<h3>You have successfully unsubscribed from this mailing list.</h3>';
		$this->load->view('thanks_msg',array('msg'=>$msg,'rc_logo'=>$rc_logo));
	}
}
?>