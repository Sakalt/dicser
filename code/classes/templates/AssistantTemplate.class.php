<?php
// Donut: open source dictionary toolkit
// version    0.11-dev
// author     Thomas de Roo
// license    MIT
// file:      AsssistantTemplate.class.php


class pAssistantTemplate extends pTemplate{

	public function renderChooserDefault($data, $section = ''){

		p::Out("<div class='btCard proper chooser'><div class='btTitle'>".(new pIcon('fa-question-circle'))." ".BATCH_CHOOSE_ASSISTANT."</div>
			<div class='btSource'>		
			<div class='btChooser'>");
		$count = 0;
		unset($data['default']);
		foreach ($data as $key => $structure) {
			p::Out("<div class='option btOptionDefault' data-role='option' data-value='".$key."'>
				<span class='btStats normal'>".$structure['desc']."</span>
				<strong>".$structure['icon']." ".$structure['surface']."</strong><br id='cl' />

				</div>");
		}
		p::Out("</div></div>

			</div>");

		
	}

	public function renderChooserTranslate($data, $section = ''){
		$count = 0;
		$output = "<div class='btCard proper chooser'><div class='btTitle'>".BATCH_CHOOSE_LANGUAGE."</div>
			".pMainTemplate::NoticeBox('fa-info-circle fa-12', BATCH_TR_DESC_START,  'notice-subtle')."
			<div class='btSource'>			<div class='btButtonBar'>
			<div class='btChooser'>";
		foreach(pLanguage::allActive() as $value){
			if($data[$value->read('id')]['percentage'] == 0)
				continue;
			$count++;
			$output .= "<div class='option btOption' data-role='option' data-value='".$value->read('id')."'>
				<span class='btStats'>".(new pIcon("playlist-check", 18))." "."<span class='per'>".round((100 - $data[$value->read('id')]['percentage']), 1)."%</span> <br />".BATCH_TR_PER_TRANS."</span><span class='btStats'>".(new pIcon("library-books", 17))." <span class='per'>".$data[$value->read('id')]['left']."</span> <br />".BATCH_TR_LEFT_TRANS."</span></span>".(new pDataField(null, null, null, 'flag'))->parse($value->read('flag'))." <strong>".$value->read('name')."</strong><br id='cl' />

				</div>";
		}
		if($count == 0){
			return $this->cardTranslateEmpty($section);
		}
		p::Out($output."</div></div>

				
			</div>
			</div>");

		
	}

	public function render($section, $data, $ajax = false, $serveCard = true){

		p::Out('<div class="assistant">');

		if($ajax == false)
			p::Out("<div class='dotsc hide'>".pMainTemplate::loadDots()."</div><div class='btLoad hide'></div><div class='btLoadSide hide'></div>");

		// If the session-chooser is already set we just load the first cards
		if(!isset($_SESSION['btChooser-'.$section])){
			$function = "renderChooser" . ucfirst($section);
			if(method_exists($this, $function))
				$this->$function($data, $section);
		}
			
	
		$hashKey = spl_object_hash($this);
		// Throwing this object's script into a session
		@pRegister::session($hashKey, $this->script($section));
		p::Out("<script type='text/javascript' src='".p::Url('pol://library/assets/js/key.js.php?key='.$hashKey)."'></script>");

		if(isset($_SESSION['btChooser-'.$section]) AND $serveCard)

			p::Out("<script type='text/javascript'>serveCard();</script>");

		if($ajax == false){
			$function = "renderBottom" . ucfirst($section);
			if(method_exists($this, $function))
				$this->$function();
		}
		

		p::Out('</div>');
	}

	public function renderBottomTranslate(){
		return p::Out("<div class='btCard bottomCard'>".pMainTemplate::NoticeBox('fa-info-circle fa-12', BATCH_TR_DESC1 . " " . sprintf(BATCH_TR_DESC2, '<span class="imitate-tag">', '</span>', '<span class="imitate-tag">', '</span>'),  'notice-subtle')."</div>");
	}

	public function cardTranslate($data, $section){
		$lang0 = new pLanguage(0);
		$lang1 = new pLanguage(pRegister::session()['btChooser-translate']);
		$data['id'] = $data['word_id'];
		$lemma = new pLemma($data, 'words');
		// Building the guideline words
		$guideLines = $this->_data->getGuideLineWords($data['word_id']);
		$guideLinesStr = '';
		foreach($guideLines as $key => $languageK){
			$language = new pLanguage($key);
			$guideLinesStr .= '<strong>'.(new pDataField(null, null, null, 'flag'))->parse($language->read('flag'));
			$items = array();
			foreach($languageK as $item)
				$items[] = $item['translation'];
			$guideLinesStr .= " ".implode(', ', $items)."<br />";
		}

		if($guideLinesStr != '')
			p::Out("<div class='btCardHelper' style='display: none'>
				<span class='btBlue'>".BATCH_OTHER_LANGUAGES."</span><br />
				".$guideLinesStr."
			</div>");
		p::Out("
			<div class='btCard transCard proper'>
				<div class='btTitle'>
				<a class='btFloat float-right button-back ttip' href='javascript:void();'>
						".(new pIcon('fa-level-up'))." ".BATCH_TR_GO_BACK."
					</a>
				".BATCH_TRANSLATE."</div>
				<div class='btSource'>
					<span class='btLanguage inline-title small'>".(new pDataField(null, null, null, 'flag'))->parse($lang0->read('flag'))." ".$lang0->read('name')."</span><br /><span class='native'>
					<strong class='pWord xxmedium'><a>".$data['native']."</a></strong></span> ".$lemma->renderStatus()." ".$lemma->generateInfoString()."
				</div><br />
				<div class='btTranslate'>
					<span class='btLanguage inline-title small'>".(new pDataField(null, null, null, 'flag'))->parse($lang1->read('flag'))." ".$lang1->read('name')."</span><br />
					<textarea placeholder='' class='elastic nWord btInput translations'></textarea>
				</div><br />
				<div class='btButtonBar'>
					<a class='btAction button-never medium no-float'>".BATCH_TR_UNTRANS."</a>
					<a class='btAction button-skip medium no-float'>".BATCH_TR_SKIP."</a>
					<a class='btAction button-handle blue medium'>".BATCH_CONTINUE."</a>
					<br id='cl' />
				</div>
		</div>
		
		<script type='text/javascript'>
			$(document).ready(function(){
				$('.ttip').tooltipster({animation: 'grow'});
				$('.translations').tagsInput({
							'defaultText': '".BATCH_TR_PLACEHOLDER."',
							'delimiter': '//',
						});
				$('.translations').elastic();

			});
			$('.button-skip').click(function(){
				$('.btLoadSide').load('".p::Url('?assistant/'.$section.'/skip/ajax')."', {'skip': ".$data['word_id']."}, function(){
					serveCard();
				});
			});
			$('.button-never').click(function(){
				$('.btLoadSide').load('".p::Url('?assistant/'.$section.'/never/ajax')."', {'never': ".$data['word_id']."}, function(){
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
		p::Out("<div class='btCard btCardEmpty transCard proper'>
				<div class='btTitle'>".BATCH_TRANSLATE."</div>
				<div class='center'><span class='inline-icon'>".(new pIcon('translate', 30))."</span>
				".pMainTemplate::NoticeBox('', sprintf(BATCH_TR_EMPTY, '<br />', '<a href="javascript:void(0);" class="button-back">', '</a>'),  'notice-subtle xmedium')."</div>
				<div class='btButtonBar'>
					
				</div>
		</div>
		<script type='text/javascript'>
		$('.button-back').click(function(){
				$('.btCardEmpty').hide();
				$('.bottomCard').hide();
				$('.btLoad').load('".p::Url('?assistant/'.$section.'/reset/ajax')."', {'translations': $('.translations').val()}, function(){
					serveCard();
				});
			});
			
		</script>
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

		$('.btOptionDefault').click(function(){
			$('.chooser').slideUp();
			$('.dotsc').slideDown();
			$('.assistant').load('".p::Url('?assistant/')."' + $(this).data('value') + '/ajaxLoad', {}, function(){
				
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
				$('.btCardHelper').hide().attr('style', '').slideDown();
			});
		};
		";
	}

}