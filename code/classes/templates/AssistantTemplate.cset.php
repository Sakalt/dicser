<?php

	// 	Donut 				🍩 
	//	Dictionary Toolkit
	// 		Version a.1
	//		Written by Thomas de Roo
	//		Licensed under MIT

	//	++	File: AsssistantTemplate.cset.php


class pAssistantTemplate extends pTemplate{

	public function renderChooserTranslate($data){

		p::Out("<div class='btCard proper chooser'><div class='btTitle'>".BATCH_CHOOSE_LANGUAGE."</div>
			".pMainTemplate::NoticeBox('fa-info-circle fa-12', BATCH_TR_DESC_START,  'notice-subtle')."
			<div class='btSource'>			<div class='btButtonBar'>
			<div class='btChooser'>");
		$count = 0;
		foreach(pLanguage::allActive() as $value){
			if($data[$value->read('id')]['percentage'] == 0)
				continue;
			$count++;
			p::Out("<div class='option btOption' data-role='option' data-value='".$value->read('id')."'>
				".(new pDataField(null, null, null, 'flag'))->parse($value->read('flag'))." <strong>".$value->read('name')."</strong><br /><span class='dType'>".BATCH_TR_PER_TRANS."<span class='per'>".round((100 - $data[$value->read('id')]['percentage']), 1)."%</span><br />".BATCH_TR_LEFT_TRANS."<span class='per'>".$data[$value->read('id')]['left']."</span></span>

				</div>");
		}
		if($count == 0){
			p::Out(pMainTemplate::NoticeBox('fa-info-circle fa-12', BATCH_TR_DESC_START,  'notice-subtle'));
		}
		p::Out("</div></div>

				
			</div>
			</div>");

		
	}

	public function render($section, $data, $ajax = false){

		p::Out("<div class='dotsc hide'>".pMainTemplate::loadDots()."</div><div class='btLoad hide'></div><div class='btLoadSide hide'></div>");

		// If the session-chooser is already set we just load the first cards
		if(!isset($_SESSION['btChooser-'.$section])){
			$function = "renderChooser" . ucfirst($section);
			if(method_exists($this, $function))
				$this->$function($data);
		}
			
	
		$hashKey = spl_object_hash($this);
		// Throwing this object's script into a session
		@pRegister::session($hashKey, $this->script($section));
		p::Out("<script type='text/javascript' src='".p::Url('pol://library/assets/js/key.js.php?key='.$hashKey)."'></script>");

		if(isset($_SESSION['btChooser-'.$section]))
			p::Out("<script type='text/javascript'>serveCard();</script>");

		if($ajax == false){
			$function = "renderBottom" . ucfirst($section);
			if(method_exists($this, $function))
				$this->$function();
		}
		
	}

	public function renderBottomTranslate(){
		return p::Out("<div class='btCard bottomCard'>".pMainTemplate::NoticeBox('fa-info-circle fa-12', BATCH_TR_DESC1 . " " . sprintf(BATCH_TR_DESC2, '<span class="imitate-tag">', '</span>', '<span class="imitate-tag">', '</span>'),  'notice-subtle')."</div>");
	}

	public function cardTranslate($data, $section){
		$lang0 = new pLanguage(0);
		$lang1 = new pLanguage(pRegister::session()['btChooser-translate']);
		$lemma = new pLemma($data['id']);
		p::Out("
			<div class='btCard transCard proper'>
				<div class='btTitle'>
				<a class='btFloat float-right button-back ttip' href='javascript:void();'>
						".(new pIcon('fa-level-up'))." ".BATCH_TR_GO_BACK."
					</a>
				".BATCH_TRANSLATE."</div>
				<div class='btSource'>
					<span class='btLanguage inline-title small'>".(new pDataField(null, null, null, 'flag'))->parse($lang0->read('flag'))." ".$lang0->read('name')."</span><br /><span class='native'>
					<strong class='pWord xxmedium'><a>".$data['native']."</a></strong></span> ".$lemma->generateInfoString()."
				</div><br />
				<div class='btTranslate'>
					<span class='btLanguage inline-title small'>".(new pDataField(null, null, null, 'flag'))->parse($lang1->read('flag'))." ".$lang1->read('name')."</span><br />
					<textarea placeholder='' class='elastic nWord btInput translations'></textarea>
				</div><br />
				<div class='btButtonBar'>
					<a class='btAction button-never no-float'>".BATCH_TR_UNTRANS."</a>
					<a class='btAction button-handle blue'>".BATCH_CONTINUE."</a>
					<a class='btAction button-skip'>".BATCH_TR_SKIP."</a>
					<br id='cl' />
				</div>
		</div>
		
		<script type='text/javascript'>
			$(document).ready(function(){
				$('.ttip').tooltipster({animation: 'grow'});
				$('.translations').tagsInput({
							'defaultText': '".BATCH_TR_PLACEHOLDER."'
						});
				$('.translations').elastic();

			});
			$('.button-skip').click(function(){
				$('.btLoadSide').load('".p::Url('?assistant/'.$section.'/skip/ajax')."', {'skip': ".$data['id']."}, function(){
					serveCard();
				});
			});
			$('.button-never').click(function(){
				$('.btLoadSide').load('".p::Url('?assistant/'.$section.'/never/ajax')."', {'never': ".$data['id']."}, function(){
					serveCard();
				});
			});
			$('.button-handle').click(function(){
				$('.btLoad').load('".p::Url('?assistant/'.$section.'/handle/ajax')."', {'translations': $('.translations').val()}, function(){
					serveCard();
				});
			});
			$('.button-back').click(function(){
				$('.btLoad').load('".p::Url('?assistant/'.$section.'/reset/ajax')."', {'translations': $('.translations').val()}, function(){
					serveCard();
				});
			});
			
		</script>
		");
	}
	
	public function cardTranslateEmpty($section){
		p::Out("<div class='btCard transCard proper'>
				<div class='btTitle'>".BATCH_TRANSLATE."</div>
				".pMainTemplate::NoticeBox('fa-info-circle fa-12', sprintf(BATCH_TR_EMPTY, '<a href="'.p::Url('?assistant/'.$section).'">', '</a>'),  'notice-subtle')."
				<div class='btButtonBar'>
					
				</div>
		</div>
		
		");
	}

	public function script($section){
		return "
		
		$('.btOption').click(function(){
			$('.chooser').slideUp();
			$('.dotsc').slideDown();
			$('.btLoad').load('".p::Url('?assistant/'.$section.'/choose/ajax')."', {'btChooser': $(this).data('value')}, function(){
				serveCard(); 
			});

		});
		function serveCard(){
			$('.btLoad').slideUp();
			$('.bottomCard').hide();
			$('.dotsc').slideDown();
			$('.btLoad').load('".p::Url('?assistant/'.$section.'/serve/ajax')."', {}, function(){
				$('.dotsc').slideUp();
				$('.btLoad').slideDown();
				$('.bottomCard').show();
			});
		};
		";
	}

}