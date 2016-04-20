<?php
	$msg_box = ""; // в этой переменной будем хранить сообщения формы
	$errors = array(); // контейнер для ошибок
	// проверяем корректность полей
	if($_POST['user_name_f'] == "")
		$errors[] = "Поле 'Имя' не заполнено!<br/>";
	if($_POST['user_phone_f'] == "")
		$errors[] = "Поле 'Телефон' не заполнено!<br/>";
	if(!$_POST['user_email_f']) {
		$errors[] = 'Поле «E-mail» не заполнено!<br/>';
	}elseif (!filter_var($_POST['user_email_f'], FILTER_VALIDATE_EMAIL)) {
		$errors[] = 'Поле «E-mail» заполнено некорректно<br/>';
	}
	if($_POST['user_text_f'] == "") 	 $errors[] = "Поле 'Сообщение' не заполнено!<br/>";

	// если форма без ошибок
	if(empty($errors)){

		// отправка лида - для костыля и решение
		defined( '_JEXEC' ) or define('_JEXEC', true);
		$rsform_b24_plugin_path = __DIR__.'/plugins/system/rsform_b24/';
		if(file_exists($rsform_b24_plugin_path.'rest.php')) {

			include $rsform_b24_plugin_path.'rest.php';

			$title = 'Заявка на корпоратив - '.time();
			$form = array(
				'name' => $_POST['user_name_f'],
				'phone' => $_POST['user_phone_f'],
				'email' => $_POST['user_email_f'],
				'message' => $_POST['user_text_f'],
			);

			rsform_b24_send_lead($title, $form);
		}

		// собираем данные из формы
		
		$message  .= "<h2>Заявка на корпоратив</h2><hr/>";
		$message  .= "<p><strong>Телефон:</strong> " . $_POST['user_phone_f'] . "</p>";
		$message  .= "<p><strong>Имя:</strong> " . $_POST['user_name_f'] . "</p>";
		$message .= "<p><strong>E-mail:</strong> " . $_POST['user_email_f'] . "</p>";
		$message .= "<p><strong>Сообщение:</strong> " . $_POST['user_text_f'] . "</p>";		
		
		// выведем сообщение об успехе
		$msg_box = "<div class='alert alert-success' role='alert'>Спасибо, мы свяжемся с вами в ближайшее время!<br><br>
					<strong>Не хотите ждать? Просто позвоните по номеру 8(812) 996-00-64</strong></div>";
		
		$status = 'OK';
		
		// отправим письмо
		if (!$_POST['user_data_f'])
			send_mail($message);
		include "send/check.php";
	}
	else{
		// если были ошибки, то выводим их
		$msg_box = "";
		foreach($errors as $one_error){
			$msg_box .= "<span class='text-danger' role='alert'>$one_error</span>";
		}
		
		$status = 'ERROR';
	}

	// делаем ответ на клиентскую часть в формате JSON
	echo json_encode(array(
		'result'	=> $msg_box,
		'status'	=> $status
	));
	
	// функция отправки письма
	function send_mail($message){
		// почта, на которую придет письмо
		//$mail_to = 'sebafurego@yandex.ru';
		$mail_to = 'info@stimul-tb.ru, event@stimul-tb.ru, vityan9376@yandex.ru, service@highway-prod.com'; 
		// тема письма
		$subject = "Заявка на корпоративное мероприятие";
		
		// заголовок письма
		$headers= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=utf-8\r\n"; // кодировка письма
		$headers .= "From: Сайт стимул <info@stimul-tb.ru>\r\n"; // от кого письмо
		
		// отправляем письмо 
		mail($mail_to, $subject, $message, $headers);
	}
	
