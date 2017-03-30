<?php

	// 	Donut 				🍩 
	//	Dictionary Toolkit
	// 		Version a.1
	//		Written by Thomas de Roo
	//		Licensed under GNUv3

	//	++	File: forms.cset.php

class pMagicField{

	private $_field, $_class, $_value, $_select, $_is_null;

	public $name;

	// Type can be: textarea, textarea_md, input, boolean, select, etc.
	public function __construct($field, $value = '', $isNull = false){
		$this->_field = $field;
		$this->_value = $value;
		$this->name = $field->name;
		$this->_is_null = $isNull;
	}

	public function value($set = false){
		if($set != false)
			return $this->_value = $set;
		else
			return $this->_value;
	}
	
	private function prepareSelect(){

	}

	public function render(){

		if($this->_field->disableOnNull AND $this->_is_null)
			return false;

		pOut("<div class='btSource'><span class='btLanguage'>".$this->_field->surface."</span>");
		// If required show an asterisk
		if($this->_field->required == true)
			pOut("<span class='xsmall' style='color: darkred;opacity: .3;'>*</span>");
		pOut("<br />");
		// input
		switch ($this->_field->type) {
			case 'textarea':
				pOut("<td><textarea name='".$this->_field->name."' class=field_'".$this->_field->name." ".$this->_field->class."'>".$this->_field->_value."</td>");
				break;

			case 'boolean':
				pOut("<span class='btNative'><select name='".$this->name."'' class='field_".$this->name." ".$this->_field->class."'><option value='1' ".($this->value() == 1 ? 'selected' : '').">".DL_ENABLED."</option><option value='0' ".($this->value() == 0 ? 'selected' : '').">".DL_DISABLED."</option></select></span>");
				break;
			
			default:
				pOut("<span class='btNative'><input name='".$this->_field->name."' class='btInput nWord small normal-font field_".$this->name." ".$this->_class."' value='".$this->_value."' /></span>");
				break;
		}
		pOut("</div>");
	}

}

class pMagicActionForm{

	private $_action, $_fields, $_adminobject, $_data, $_name, $_edit, $_magicfields, $_table, $_strings, $_section, $_app, $_extra_fields;

	public function __construct($name, $table, $fields, $strings, $app, $section, $adminobject){
		$this->_name = $name;
		$this->_fields = $fields;
		$this->_table = $table;
		$this->_app = $app;
		$this->_adminobject = $adminobject;
		$this->_action = $this->_adminobject->getAction($name);
		$this->_edit = ($this->_name == 'edit');
		$this->_section = $section;
		$this->_strings = $strings;
		$this->_magicfields = new pSet;
		if($this->_edit)
			$this->_data = $this->_adminobject->data();
		else
			$this->_data = array();
		$this->_name = ($this->_edit ? 'edit' : 'new');
	}

	public function compile(){
		foreach ($this->_fields->get() as $field) {
			if($this->_edit)
				$this->_magicfields->add(new pMagicField($field, $this->_data[0][$field->name], ($this->_data[0]['id'] == 0)));
			else
				$this->_magicfields->add(new pMagicField($field));
		}
	}

	public function ajax(){

		// Checking if we have any empty required fields.
		$empty_error = 0;
		foreach($this->_fields->get() as $field)
			if($field->required AND $_REQUEST['admin_form_'.$field->name] == '')
				$empty_error++;

		if($empty_error != 0){
			pOut(pNoticeBox('fa-warning fa-12', $this->_strings[3], 'danger-notice ajaxMessage'));
		}
		else{

			// Preparing the values
			$values = array();
			foreach($this->_fields->get() as $field)
				$values[] = $_REQUEST['admin_form_'.$field->name];

			try {

				// Editing
				if($this->_edit){
					$this->_adminobject->dataObject->prepareForUpdate($values);
					$this->_adminobject->dataObject->update();
				}
				//Adding
				else{
					$this->_adminobject->dataObject->prepareForInsert($values);
					$this->_adminobject->dataObject->insert();
				}

				pOut(pNoticeBox('fa-check fa-12', $this->_strings[5].". <a href='".pUrl("?".$this->_app."&section=".$this->_section. (isset($_REQUEST['position']) ? "&offset=".$_REQUEST['position'] : ""))."'>".$this->_strings[6]."</a>", 'succes-notice ajaxMessage'));

			} catch (Exception $e) {
				pOut(pNoticeBox('fa-warning fa-12', $this->_strings[4], 'danger-notice ajaxMessage'));
			}

		}
		pOut("<script type='text/javascript'>
				$('.saving').slideUp(function(){
					$('.ajaxMessage').slideDown();
				});
			</script>");

	}


	public function form(){
		pOut("<div class='btCard admin'>");
		pOut("<div class='btTitle'>".$this->_strings[0]."</div>");

		pOut(pNoticeBox('fa-spinner fa-spin fa-12', $this->_strings[2], 'notice saving hide'));

		// That is where the ajax magic happens:
		pOut("<div class='ajaxSave'></div>");

		foreach($this->_magicfields->get() as $magicField){
			$magicField->render();
		}
		pOut("<div class='btButtonBar'>
			<a class='btAction wikiEdit' href='".pUrl("?".$this->_app."&section=".$this->_section.(isset($_REQUEST['position']) ? "&offset=".$_REQUEST['position'] : ""))."'><i class='fa fa-12 fa-arrow-left' ></i> ".BACK."</a>
			<a class='btAction green submit-form'><i class='fa fa-12 fa-check-circle'></i> ".$this->_strings[1]."</a><br id='cl'/></div>");
		pOut("</div>");
		$loadValues = array();

		foreach ($this->_fields->get() as $field){
			if($this->_edit AND $field->disableOnNull AND $this->_data[0]['id'] == 0)
				$loadValues[] = "'admin_form_".$field->name."': '".$this->_data[0][$field->name]."'";
			else 
				$loadValues[] = "'admin_form_".$field->name."': $('.field_".$field->name."').val()";
		}

		pOut("<script type='text/javascript'>
			".(!(isset($this->_adminobject->_structure[$this->_section]['disable_enter']) AND $this->_adminobject->_structure[$this->_section]['disable_enter'] != true) ? "$(window).keydown(function(e) {
			    		switch (e.keyCode) {
			       		 case 13:
			       			 $('.submit-form').click();
			   			 }
			   		 return; 
					});" : '')."
				$('.btCard select').select2();
				$('.submit-form').click(function(){
					$('.saving').slideDown();
					$('.ajaxSave').load('".pUrl("?".$this->_app."&section=".$this->_section."&action=".$this->_name.(($this->_edit) ? '&id='.$this->_data[0]['id'] : '')."&ajax")."', {
						".implode(", ", $loadValues)."
					});
				});
			</script>");
	}


	public function newLinkPrepare($linkObject, $show_parent, $show_child, $doExtraFields = false, $fields = null){
		$this->_linkObject = $linkObject->_adminObject;
		$this->_guestObject = $linkObject;
		$this->_show_parent = $show_parent;
		$this->_show_child = $show_child;
		if($doExtraFields)
			$this->_extra_fields = $fields;
		else
			$this->_extra_fields = null;
	}

	public function newLinkAjax(){

		$empty_error = 0;

		if($_REQUEST['admin_form_'.$this->_guestObject->structure[$this->_section]['incoming_links'][$this->_guestObject->_section]['parent']] == null){
			$empty_error++;
		}

		foreach ($this->_extra_fields as $field)
			if($field->showInForm && $field->required)
				if(isset($_REQUEST['admin_form_'.$field->name])AND empty($_REQUEST['admin_form_'.$field->name]))
						$empty_error++;


		if($empty_error == 0){

			// We have to check if the relation already exists
			if($this->_adminobject->dataObject->countAll($this->_guestObject->structure[$this->_section]['incoming_links'][$this->_guestObject->_section]['child'] . " = '" . $this->_adminobject->_matchOnValue . "' AND " . $this->_guestObject->structure[$this->_section]['incoming_links'][$this->_guestObject->_section]['parent'] . " = '" . $_REQUEST['admin_form_'.$this->_guestObject->structure[$this->_section]['incoming_links'][$this->_guestObject->_section]['parent']] . "'")){

				pOut(pNoticeBox('fa-info-circle fa-12', DA_TABLE_RELATION_EXIST.". <a href='".pUrl("?".$this->_app."&section=".$this->_section."&action=link-table&id=".$this->_linkObject->data()[0]['id']."&linked=".$this->_guestObject->_data['section_key'])."'>".$this->_strings[6]."</a>", 'notice ajaxMessage'));

			}
			else{
				// Time to insert
					// Preparing the values
			
				foreach($this->_fields->get() as $field)
					if($field->name == $this->_guestObject->structure[$this->_section]['incoming_links'][$this->_guestObject->_section]['child'])
						$values[] = $this->_adminobject->_matchOnValue;
					else
						$values[] = $_REQUEST['admin_form_'.$field->name];

				$this->_adminobject->dataObject->prepareForInsert($values);
				$this->_adminobject->dataObject->insert();
				$this->_adminobject->dataObject->getSingleObject(1);
				pOut(pNoticeBox('fa-info-circle fa-12', DA_TABLE_RELATION_ADDED.". <a href='".pUrl("?".$this->_app."&section=".$this->_section."&action=link-table&id=".$this->_linkObject->data()[0]['id']."&linked=".$this->_guestObject->_data['section_key'])."'>".$this->_strings[6]."</a>", 'succes-notice ajaxMessage'));
			}
		}
		else
			pOut(pNoticeBox('fa-warning fa-12', $this->_strings[3], 'danger-notice ajaxMessage'));

		pOut("<script type='text/javascript'>
				$('.saving').slideUp(function(){
					$('.ajaxMessage').slideDown();
				});
			</script>");

	}

	public function newLinkForm(){

		pOut("<div class='btCard admin link-table'>");
		pOut("<div class='btTitle'><i class='fa fa-plus-circle'></i> ".DA_TABLE_NEW_RELATION."<span class='medium'>".$this->_adminobject->_surface."</span></div>");

		pOut(pNoticeBox('fa-spinner fa-spin fa-12', $this->_strings[2], 'notice saving hide'));

		// That is where the ajax magic happens:
		pOut("<div class='ajaxSave'></div>");
		pOut("<div class='btSource'><span class='btLanguage'>".DA_TABLE_LINKS_PARENT."</span><br />
			<span class='btNative'>".$this->_linkObject->data()[0][$this->_show_parent]."</span></div>");

		pOut("<div class='btSource'><span class='btLanguage'>".DA_TABLE_LINKS_CHILD."</span><br /><span class='btNative'><select class='field_".$this->_guestObject->structure[$this->_section]['incoming_links'][$this->_guestObject->_section]['parent']."'>");

		foreach($this->_adminobject->_passed_data as $data)
			pOut("<option value='".$data['id']."'>".$data[$this->_show_child]."</option>");

		pOut("</select></span></div>");


		// Extra fields	
		if($this->_extra_fields != null)
			foreach($this->_extra_fields as $field)
				(new pMagicField($field))->render();

		pOut("<div class='btButtonBar'>
			<a class='btAction wikiEdit' href='".pUrl("?".$this->_app."&section=".$this->_section."&action=link-table&id=".$this->_linkObject->data()[0]['id']."&linked=".$this->_guestObject->_data['section_key'])."'><i class='fa fa-12 fa-arrow-left' ></i> ".BACK."</a>
			<a class='btAction green submit-form'><i class='fa fa-12 fa-check-circle'></i> ".$this->_strings[1]."</a><br id='cl'/></div>");
		pOut("</div>");

		$loadValues = array();

		foreach ($this->_fields->get() as $field){
			if($this->_edit AND $field->disableOnNull AND $this->_data[0]['id'] == 0)
				$loadValues[] = "'admin_form_".$field->name."': '".$this->_data[0][$field->name]."'";
			else 
				$loadValues[] = "'admin_form_".$field->name."': $('.field_".$field->name."').val()";
		}

		pOut("<script type='text/javascript'>
				".(!(isset($this->_guestObject->structure[$this->_section]['incoming_links']['disable_enter']) AND $this->_guestObject->structure[$this->_section]['incoming_links']['disable_enter'] != true) ? "$(window).keydown(function(e) {
			    		switch (e.keyCode) {
			       		 case 13:
			       			 $('.submit-form').click();
			   			 }
			   		 return; 
					});" : '')."
				$('.field_".$this->_guestObject->structure[$this->_section]['incoming_links'][$this->_guestObject->_section]['parent']."').select2();
				$('.submit-form').click(function(){
					$('.saving').slideDown();
					$('.ajaxSave').load('".pUrl("?".$this->_app."&section=".$this->_section."&action=new-link&id=".$this->_linkObject->data()[0]['id']."&linked=".$this->_guestObject->_data['section_key'])."&ajax', {".implode(", ", $loadValues)."});
				});
			</script>");
	}

}