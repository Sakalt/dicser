<?php

	// 	Donut 				🍩 
	//	Dictionary Toolkit
	// 		Version a.1
	//		Written by Thomas de Roo
	//		Licensed under MIT

	//	++	File: MenuTemplate.cset.php

// Accepts a RuleDataModel as $data parameter

class pRulesheetTemplate extends pTemplate{

	public function renderAll(){
		// The home search box! Only if needed!
		if(!(isset(pAdress::arg()['ajax']) and isset(pAdress::arg()['nosearch'])))
			pOut(new pSearchBox(true));

		pOut("<br/ ><div class='home-margin pEntry'>".pMarkdown(file_get_contents(pFromRoot("static/home.md")), true)."</div><br />");
	}

	public function ruleTypeWatch($section){
		return "<script type='text/javascript'>
				var options = {
			    	callback: function (value) {
			    		$('.describeStatement').load('".pUrl('?rulesheet/'.$section.'/describe/ajax')."', {'rule' : $('#rule-content').val()});
			    	},
			    	wait: 200,
			   		highlight: true,
			    	allowSubmit: false,
			    	captureLength: 1,
				};
				$('#rule-content').typeWatch( options );
				</script>";
	}

	public function rulesheetForm($section, $edit = false){
		if($edit)
			$data = $this->_data->data()->fetchAll()[0];
		pOut("<div class='left'>
			<div class='btCard rulesheetCard'>
				<div class='btTitle'>Rule</div>
				<div class='btSource'><span class='btLanguage'>Name <span class='xsmall' style='color: darkred;opacity: 1;'>*</span></span><br />
				<span class='btNative'><input class='btInput nWord small normal-font' value='".($edit ? $data['name'] : '')."'/></span></div>
				<div class='btSource'><span class='btLanguage'>Statement</span><br />
				<span class='btNative'><textarea placeholder='prefix [stem] suffix' spellcheck='false' class='btInput Rule elastic allowtabs' id='rule-content'>".($edit ? $data['rule'] : '')."</textarea><div class='describeStatement'></div></span></div>
				".$this->ruleTypeWatch($section)." <br />
			</div>
		</div>
		<div class='right'>	");
		if(!in_array($section, array('phonology', 'ipa')))
			pOut("
			<div class='btCard rulesheetCard'>
				<div class='btTitle'>Selectors</div>
					<div class='notice'>".(new pIcon('fa-info-circle', 10))." The combination of the selectors decides when and where the rule is applied.</div><br />
					<div class='rulesheet inner'>
						<div class='left'>
							".pMarkdown("##### Primary selectors ")."<br />
							<div class='btSource'><span class='btLanguage'>Lexical categories <em class='small'>(part of speech)</em></span><br />
							<span class='btNative'><select class='select-lexcat select2' multiple='multiple'>".(new pSelector('types', $this->_data->_links['lexcat'], 'name', true, 'rules', true))->render()."</select></span></div>
							<div class='btSource'><span class='btLanguage'>Grammatical categories</span><br />
							<span class='btNative'><select class='select-gramcat select2' multiple='multiple'>".(new pSelector('classifications', $this->_data->_links['gramcat'], 'name', true, 'rules', true))->render()."</select></span></div>
							<div class='btSource'><span class='btLanguage'>Grammatical tags</span><br />
							<span class='btNative'><select class='select-tags select2' multiple='multiple'>".(new pSelector('subclassifications', $this->_data->_links['tags'], 'name', true, 'rules', true))->render()."</select></span></div>
						</div>
						<div class='right'>
							".pMarkdown("##### Secondary selectors ")."<br />
							<div class='btSource'><span class='btLanguage'>Inflection tables</span><br />
							<span class='btNative'><select class='select-tables select2' multiple='multiple'>".(new pSelector('modes', $this->_data->_links['tables'], 'name', true, 'rules', true))->render()."</select></span></div>
							<div class='btSource'><span class='btLanguage'>Table headings</span><br />
							<span class='btNative'><select class='select-headings select2' multiple='multiple'>".(new pSelector('submodes', $this->_data->_links['headings'], 'name', true, 'rules', true))->render()."</select></span></div>
							<div class='btSource'><span class='btLanguage'>Table rows</span><br />
							<span class='btNative'><select class='select-rows select2' multiple='multiple'>".(new pSelector('numbers', $this->_data->_links['rows'], 'name', true, 'rules', true))->render()."</select></span></div>
						</div>
				</div>");
		pOut("</div>
		</div>

		<script type='text/javascript'>
			$('.select2').select2({placeholder: 'All possible', allowClear: true});
		</script>");
	}

	public function renderNew(){
		return $this->rulesheetForm($this->activeStructure['section_key']);
	}

	public function renderEdit(){
		return $this->rulesheetForm($this->activeStructure['section_key'], true);
	}

}