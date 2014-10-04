<?php

/*
 Plugin Name: CS Feedback Forms
Description: CS Feedback Forms
Author: Dmitriy Shuba <sda@sda.in.ia>
Version: 0.0.1
*/

class CSFeedbackForms   {
    
    const SUCCESS_SUBMIT = 200;
    
    const ERROR_FORM_ID_NOTFOUND_CODE = 10;
    const ERROR_FORM_ID_NOTFOUND_MSG = 'ID forms is incorrect.';
    
    const ERROR_FIELD_REQ_CODE = 310;
    const ERROR_FIELD_REQ_MSG = 'A required field.';
    
    const ERROR_FIELD_INVALID_CODE = 320;
    const ERROR_FIELD_INVALID_MSG = 'Wrong format of field.';
    
    const ERROR_FIELD_INVALID_FIELDS_CODE = 300;
    const ERROR_FIELD_INVALID_FILEDS_MSG = 'This form was completed incorrectly.';
    
    
    protected $forms = array();
    
    public function __construct()  {
        
        $this->initForms();
        
        // Добавляем js-скрипты для front-end
        add_action('wp_enqueue_scripts', array(&$this, 'init_scripts_method'));
        
        // Добавляем стилил для front-end
        add_action('wp_enqueue_scripts', array(&$this, 'init_scripts_styles'));
        
        //получение запроса
        add_action('wp_ajax_CSFeedbackForms_submit', array(&$this, 'action_CSFeedbackForms'), 10000);
        add_action('wp_ajax_nopriv_CSFeedbackForms_submit', array(&$this, 'action_CSFeedbackForms'), 10000);
        
    }
    
    public function init_scripts_method()  {
    
        wp_enqueue_script('jquery.validate', get_home_url() . '/wp-content/plugins/cs-feedback-forms/js/jquery.validate.min.js', array('jquery'));
        wp_enqueue_script('jquery.form', get_home_url() . '/wp-content/plugins/cs-feedback-forms/js/jquery.form.min.js', array('jquery'));
        wp_enqueue_script('cs.feedback.forms', get_home_url() . '/wp-content/plugins/cs-feedback-forms/js/cs.feedback.forms.js', array('jquery', 'jquery.validate', 'jquery.form'));
        
        wp_enqueue_script('general_action', get_home_url() . '/wp-content/plugins/cs-feedback-forms/js/general_action.js', array('jquery', 'jquery.validate', 'jquery.form', 'cs.feedback.forms'));
        
    }
    
    public function init_scripts_styles()  {
    
        //wp_enqueue_style('',  'style.css', array());
    
    }
    
    private function initForms() {
        
        $forms = glob(ABSPATH . '/wp-content/plugins/cs-feedback-forms/forms/*/config.php');
        
        foreach ($forms as $form)   {
            
            $form_id = basename($this->getBaseDir($form));
            
            $this->forms[$form_id] = require_once($form);
            
            //подключаем JS скрипты
            $js_success_serv = $this->getBaseDir($form) . '/js/actions.js';
            $js_success_http = str_replace(ABSPATH, NULL, $js_success_serv);
            
            if(is_file($js_success_serv)) {
                wp_enqueue_script('cs.feedback.forms.' . $form_id, get_home_url() . $js_success_http, array('jquery', 'cs.feedback.forms'));
            }
            
            //подключаем почтовые шаблоны
            $mail_backend = $this->getBaseDir($form) . '/template-email/backend.tpl';
            if(is_file($mail_backend)) {
                $this->forms[$form_id]['mail-template']['backend'] = file_get_contents($mail_backend);
            }
            
            $mail_frontend = $this->getBaseDir($form) . '/template-email/frontend.tpl';
            if(is_file($mail_frontend)) {
                $this->forms[$form_id]['mail-template']['frontend'] = file_get_contents($mail_backend);
            }
            
        }
        
        //print_r($this->forms);
        
        
    }
    
    
    private function prepareFormData($form_id, $data = array())    {
        
        foreach ($this->forms[$form_id]['fields'] as $name => $field)    {
            
            if(isset($data[$name])) {
                
                $this->forms[$form_id]['fields'][$name]['value'] = $data[$name];
                
            }
            else {
                
                $this->forms[$form_id]['fields'][$name]['value'] = NULL;
                
            }
            
        }
        
    }
    
    private function validFormData($form_id)    {
        
        $error = array();
        
        foreach ($this->forms[$form_id]['fields'] as $name => $field)    {
            
            if(($field['required'] == true) && (empty($field['value'])))    {
                
                $this->forms[$form_id]['fields']['error'] = self::ERROR_FIELD_REQ_CODE;
                $error[$name] = array(self::ERROR_FIELD_REQ_CODE);
                continue;
                
            }
            
            switch ($field['valid'])    {
                case 'email' : {
                    
                    if((!empty($field['value'])) && (!filter_var($field['value'], FILTER_VALIDATE_EMAIL))) {
                        
                        $this->forms[$form_id]['fields']['error'] = self::ERROR_FIELD_INVALID_CODE;
                        $error[$name] = array(self::ERROR_FIELD_INVALID_CODE);
                        continue;
                        
                    }
                    
                } break;
            }
            
        }
        
        return $error;
        
    }
    
    /**
     * Заполняем поля переданные через форму
     * @param string $form_id
     */
    private function prepareEmailTemplateForPost($form_id)  {
        
        foreach ($this->forms[$form_id]['mail-template'] as $template_name => $message)  {
            
            if(!empty($message))   {
                
                foreach ($this->forms[$form_id]['fields'] as $name => $field)    {
                
                    switch ($field['format']) {
                    	default: {
                
                    	    $message = str_replace('['.$name.']', $field['value'], $message);
                
                    	} break;
                    }
                
                }
                $this->forms[$form_id]['mail-message'][$template_name] = $message;
            
            }
            
        }
        
        
    }
    
    /**
     * Выполняет отправку сообщений
     * @param string $form_id
     */
    private function sendMessage($form_id) {
        
        $emails = array();
        if((!empty($this->forms[$form_id]['sendto']['admins'])) && (!empty($this->forms[$form_id]['mail-message']['backend'])))    {
            
            if(!empty($this->forms[$form_id]['sendto']['admins']['to_admin_email']))  {
                
                $emails[] = get_bloginfo( 'admin_email' );
                
            }
            
            if((!empty($this->forms[$form_id]['sendto']['admins']['emails'])) && (is_array($this->forms[$form_id]['sendto']['admins']['emails'])))  {
                
                $emails = array_merge($emails, $this->forms[$form_id]['sendto']['admins']['emails']);
                
            }
            
            foreach ($emails as $index => $email)   {
            
                if(!filter_var($email, FILTER_VALIDATE_EMAIL))  {
                    unset($email[$index]);
                }
            
            }
            
            $message = $this->forms[$form_id]['mail-message']['backend'];
            
            $res = wp_mail($emails, $this->forms[$form_id]['subject'], $message, array());
            
        }
          
        
    }
    
    public function action_CSFeedbackForms()	{
        
        header('Content-Type: application/json');
        
        $form_id = $_POST['form_id'];
        
        if(array_key_exists($form_id, $this->forms))  {
            
            $form = $this->forms[$form_id];
            
            $this->prepareFormData($form_id, $_POST); //print_r($this->forms);
            $error = $this->validFormData($form_id);
            
            if(empty($error))   {
                
                $this->prepareEmailTemplateForPost($form_id); //print_r($this->forms);
                $this->sendMessage($form_id);
                
                echo json_encode(array(
                        'result'	=> 'OK',
                        'form_id' => $form_id,
                        'message'	=>	'',
                        'code'		=>	self::SUCCESS_SUBMIT,
                ));
                
            }
            else {
                
                
                echo json_encode(array(
                        'result'	=> 'ERROR',
                        'form_id' => $form_id,
                        'error'   => $error,
                        'message'	=>	__(self::ERROR_FIELD_INVALID_FILEDS_MSG, 'cs-feedback-forms'),
                        'code'		=>	self::ERROR_FIELD_INVALID_FIELDS_CODE,
                ));
                
            }
            
             
            
            //print_r($form);
            
        }
        else {
            
            echo json_encode(array(
                    'result'	=> 'ERROR',
                    'form_id' => $form_id,
                    'message'	=>	__(self::ERROR_FORM_ID_NOTFOUND_MSG, 'cs-feedback-forms'),
                    'code'		=>	self::ERROR_FORM_ID_NOTFOUND_CODE,
            ));
            
        }
        
        
        exit();
        
    }
    
    private function getBaseName($filename) {
    
        $path_info = pathinfo($filename);
        return $path_info['filename'];
    
    }
    
    private function getBaseDir($filename) {
    
        $path_info = pathinfo($filename);
        return $path_info['dirname'];
    
    }
    
}


$csff = new CSFeedbackForms();