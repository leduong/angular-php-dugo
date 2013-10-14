<?php
// Auto-add the session token (ignored if not using sessions)
if(isset($validation) AND $error = $validation->error('token'))
{
	print html::tag('div', $error, array('class'=>'error'));
}

print html::tag('input', 0, array('type'=>'hidden','value'=>session('token'),'name'=>'token'));


foreach($fields as $field => $data)
{
	print "\n\n<div".(isset($data['div'])?html::attributes($data['div']):'').'>';

	if( ! isset($data['attributes']['type']) OR ! in_array($data['attributes']['type'], array('hidden','submit','reset','button')))
	{
		if(isset($data['required'])) $data['label'] .= html::tag('span', $data['required'], array('class'=>'required'));
		print html::tag('label', $data['label'], array('for'=>$field, 'class'=>'control-label'));
	}

	if($data['type'] === 'select') // Select box
	{
		print "<div class='controls'>";
		print html::select($field, $data['options'], $data['value'], $data['attributes']);
		print "</div>";
	}
	elseif($data['type'] === 'textarea') // Textarea
	{
		if(isset($data['editor'])) print html::tag('div', $data['editor']);
		print html::tag($data['type'], str($data['value']), $data['attributes']);
	}
	elseif($data['attributes']['type'] === 'datetime') // Special datetime type
	{
		print html::datetime($data['value'], $field);
	}
	elseif($data['attributes']['type'] === 'radio') // Special radio type
	{
		print "<div class='controls'>";
		$a=array('class' => 'checkbox');
		if($data['value']) $a['checked']='checked';
		print html::tag($data['type'],0,$a+$data['attributes']);
		print "</div>";
	}
	elseif($data['attributes']['type'] === 'checkbox')
	{
		print "<div class='controls'>";

		if( isset($data['check']) && $data['check'] == $data['value'])
			$data['attributes']['checked'] = 'checked';

		print html::tag('input', 0, $data['attributes']+array('value' => str($data['value'])));

		print "</div>";
	}
	elseif($data['attributes']['type'] === 'submit')
	{
		echo '<input type="submit" value="'. $data['value'] .'" class="'. $data['class'] .'">';
	}
	else // a normal input
	{
		print "<div class='controls'>";

		if (isset($data['options']) && $options = $data['options']) foreach($options as $k => $v) {
			$a=array('value' => $k);
			if($data['value']&&in_array($k,(array) $data['value'])) $a['checked']='checked';
			print html::tag($data['type'],$v,$a+$data['attributes']);
		}
		else print html::tag($data['type'], 0, $data['attributes']+array('value' => str($data['value'])));

		print "</div>";
	}

	if(isset($validation) AND $error = $validation->error($field)) print html::tag('div', $error, array('class'=>'error'));
	if(isset($data['description'])) print html::tag('div', $data['description'], array('class'=>'help'));

	print "\n<br />\n</div>";
}