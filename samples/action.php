<?php
	$msg_box = ""; // в этой переменной будем хранить сообщения формы
	$errors = array(); // контейнер для ошибок
	// проверяем корректность полей
	switch ($_POST['submit']) {
		case 'btn_submit':
			$subject = 'Сообщение с футер формы';
			if(!$_POST['user_email']) {
				$errors[] = 'Поле «Телефон или E-mail» не заполнено!<br/>';
			}
			break;
		case 'sub_form':
			$subject = 'Новый отзыв с сайта стимул';
			if(!$_POST['text_comment']) {
				$errors[] = 'Поле «Отзыв» не заполнено!<br/>';
			}
			break;
		default:
			die;
	}

	// если форма без ошибок
	if(empty($errors)){

		// отправка лида - для костыля и решение
		defined( '_JEXEC' ) or define('_JEXEC', true);
		$rsform_b24_plugin_path = __DIR__.'/plugins/system/rsform_b24/';
		if(file_exists($rsform_b24_plugin_path.'rest.php')) {

			include $rsform_b24_plugin_path.'rest.php';

			$title = $subject.' - '.time();
			$form = array(
				'name' => $_POST['user_name'],
				'message' => $_POST['text_comment'],
			);

			// В одном поле может быть что угодно
			if(preg_match('/(^[а-яА-ЯёЁa-zA-Z0-9_\.-]{1,}@([а-яА-ЯёЁa-zA-Z0-9_-]{1,}\.){1,}[а-яА-ЯёЁa-zA-Z0-9_-]{2,}$)/iu', $_POST['user_email'])) {
				$form['email'] = $_POST['user_email'];
			} elseif (preg_match('/^\s*(?:\+?(\d{1,3}))?([-. (]*(\d{1,3})[-. )]*)?((\d{3})[-. ]*(\d{2,3})(?:[-.x ]*(\d+))?)\s*$/', $_POST['user_email'])
			|| preg_match('/^-?\d+$/', $_POST['user_email'])) {
				$form['phone'] = $_POST['user_email'];
			}

			rsform_b24_send_lead($title, $form);
		}

		// собираем данные из формы
		$message  .= "<h2>$subject</h2><hr/>";
		$message  .= "<p><strong>Имя:</strong> " . $_POST['user_name'] . "<p/>";
		$message .= "<p><strong>Телефон или E-mail:</strong> " . $_POST['user_email'] . "<p/>";
		$message .= "<p><strong>Сообщение:</strong> " . $_POST['text_comment'] . "<p/>";		
		// выведем сообщение об успехе
		$msg_box = "<div class='alert alert-success' role='alert'>Сообщение успешно отправлено!</div>";
		$status = 'OK';
		
		if (!$_POST['user_data'])
			send_mail($message, $subject); // отправим письмо
	}else{
		// если были ошибки, то выводим их
		$msg_box = "";
		foreach($errors as $one_error){
			$msg_box .= "<div class='alert alert-danger' role='alert'>$one_error</div>";
		}
		
		$status = 'ERROR';
	}

	// делаем ответ на клиентскую часть в формате JSON
	echo json_encode(array(
		'result' => $msg_box,
		'status'	=> $status
	));
	
	
	// функция отправки письма
	function send_mail($message, $subject){
		// почта, на которую придет письмо
		//$mail_to = 'sebafurego@yandex.ru';
		$mail_to = 'info@stimul-tb.ru, event@stimul-tb.ru, vityan9376@yandex.ru';
		
		// заголовок письма
		$headers= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=utf-8\r\n"; // кодировка письма
		$headers .= "From: Сайт стимул <info@stimul-tb.ru>\r\n"; // от кого письмо
		
		// отправляем письмо 
		mail($mail_to, $subject, $message, $headers);
	}
	
