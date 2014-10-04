<?php

return array(
    'subject' => get_bloginfo('name') . ' страница контактов',
    'fields' => array(
        'name' => array(
            'required' => true,
            'valid' => '',
            'mail' => array('label' => 'Имя:'),
            'format' => 'string',
        ),
        'phone' => array(
            'required' => true,
            'mail' => array('label' => 'Телефон:'),
            'format' => 'string',
        ),
        'comment' => array(
            'required' => false,
            'mail' => array('label' => 'Комментарий:'),
            'format' => 'string',
        ),
    ),
    'sendto' => array(
        // блок отвечает за отправку копии сообщения автору
        //'author'  => array(
        //        'send'         => TRUE,
        //        'by_field'     => 'bot_email',
        //),
        // блок отвечает за отправку сообщения администратору
        'admins' => array(
            'emails' => array(), // E-mail адреса на которые следует отправить сообщение
            'to_admin_email' => TRUE, // письмо будет отправлено на адрес администратора блока
        //'to_admins'        => array(),
        ),
    ),
);
