<?php
/*------------------------------------------------------- 
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
 -------------------------------------------------------- 
| Filename: atom.php
| Author: Frederick MC Chan (Hien)
| Co-Author: PHP-Fusion Development Team
 -------------------------------------------------------- 
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
 --------------------------------------------------------*/

class atom {

	public function load_theme() {

	}

	public function set_theme() {

	}

	public function theme_editor() {
		global $aidlink, $locale;
		$tab_title['title'][] = 'Base Fonts';
		$tab_title['id'][] = 'font';
		$tab_title['icon'][] = '';

		$tab_title['title'][] = 'Layout Design';
		$tab_title['id'][] = 'grid';
		$tab_title['icon'][] = '';

		$tab_title['title'][] = 'Navigations';
		$tab_title['id'][] = 'nav';
		$tab_title['icon'][] = '';

		$tab_title['title'][] = 'Table and Fieldsets';
		$tab_title['id'][] = 'panel';
		$tab_title['icon'][] = '';

		$tab_active = tab_active($tab_title, 0);

		echo openform('theme_edit', 'theme_edit', 'post', FUSION_SELF.$aidlink);
		echo opentab($tab_title, $tab_active, 'atom');
		echo opentabbody($tab_title['title'][0], $tab_title['id'][0], $tab_active);
		echo "<div class='m-t-20'>\n";
			$this->font_settings();
		echo "</div>\n";
		echo closetabbody();
		echo opentabbody($tab_title['title'][1], $tab_title['id'][1], $tab_active);
		echo closetabbody();
		echo opentabbody($tab_title['title'][2], $tab_title['id'][2], $tab_active);
		echo closetabbody();
		echo opentabbody($tab_title['title'][3], $tab_title['id'][3], $tab_active);
		echo closetabbody();

		echo closetab();
		echo closeform();
	}


	private function font_settings() {
		$data = array();
		$data['sans_serif_fonts'] = '';
		$data['serif_fonts'] = '';
		$data['monospace_fonts'] = '';
		$data['base_font'] = '';

		$base_font = array_values(array_flip($this->base_font()));
		$web_font = array_values(array_flip($this->google_font()));
		$font_list = array_merge($base_font, $web_font);
		$text_decoration = array('Normal', 'Bold', 'Underlined', 'Bold and Underlined');

		$color_options = array("placeholder" => "Choose Color", 'width'=>'100%', "format" => "hex");
		$font_options = array('width' => '100%', 'placeholder' => 'Pick the font and build your collection.', 'tags'=>1, 'multiple'=>1, 'max_select'=>6, 'inline'=>1);
		$font_type_options = array('placeholder' => 'Select a Base Font', 'width' => '280px', 'inline'=>1);
		$font_size_options = array('placeholder' => '(px)', 'width' => '250px', 'number'=>1, 'class'=>'pull-left display-inline m-r-10');
		$fonts_family_opts = array(
			'0'=>'Sans Serif Font Family',
			'1'=>'Monospace Font Family',
			'2'=>'Serif Font Family'
		);

		echo form_para("Base Font Settings", 'font_settings');
		echo "<hr>\n";
		echo form_select("Sans-Serif Collection", "sans_serif_fonts", "theme_font_family_sans_serif", $font_list, $data['sans_serif_fonts'], $font_options);
		echo form_select("Serif Collection", "serif_fonts", "theme_font_family_serif", $font_list, $data['serif_fonts'], $font_options);
		echo form_select("Monospace Collection", "monospace_fonts", "theme_font_family_monospace", $font_list, $data['monospace_fonts'], $font_options);

		echo form_select("Base Font", "base_font", "base_font", $fonts_family_opts, $data['base_font'], $font_type_options);
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_para('Base Font Sizes', 'base-font-size');
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_text("Base Font Size", "base_font_size", "base_font_size", $data['base_font_size'], $font_size_options);
		echo form_text("Line Spacing", "base_font_height", "base_font_height", $data['base_font_height'], $font_size_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_text("Base Font Size Large", "base_font_size_l", "base_font_size_l", $data['base_font_size_l'], $font_size_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_text("Base Font Size Small", "base_font_size_s", "base_font_size_s", $data['base_font_size_s'], $font_size_options);
		echo "</div>\n</div>\n";

		echo form_para("Header Font Settings", 'font_settings');
		echo "<hr>\n";
		// h1
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_para('Heading 1', 'h1');
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_text("Font Size", "font_size_h1", "font_size_h1", $data['font_size_h1'], $font_size_options);
		echo form_text("Line Spacing", "font_h1_height", "font_h1_height", $data['font_h1_height'], $font_size_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_colorpicker('Font Color', "font_color_h1", "font_color_h1", $data['font_color_h1'], $color_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_select('Font Styling', "font_decoration_h1", "font_decoration_h1", $text_decoration, $data['font_decoration_h1'], $color_options);
		echo "</div>\n</div>\n";

		// h2
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_para('Heading 2', 'h2');
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_text("Font Size", "font_size_h2", "font_size_h2", $data['font_size_h2'], $font_size_options);
		echo form_text("Line Spacing", "font_h2_height", "font_h2_height", $data['font_h2_height'], $font_size_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_colorpicker('Font Color', "font_color_h2", "font_color_h2", $data['font_color_h2'], $color_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_select('Font Styling', "font_decoration_h2", "font_decoration_h2", $text_decoration, $data['font_decoration_h2'], $color_options);
		echo "</div>\n</div>\n";

		// h3
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_para('Heading 3', 'h3');
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_text("Font Size", "font_size_h3", "font_size_h2", $data['font_size_h3'], $font_size_options);
		echo form_text("Line Spacing", "font_h3_height", "font_h3_height", $data['font_h3_height'], $font_size_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_colorpicker('Font Color', "font_color_h3", "font_color_h3", $data['font_color_h3'], $color_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_select('Font Styling', "font_decoration_h3", "font_decoration_h3", $text_decoration, $data['font_decoration_h3'], $color_options);
		echo "</div>\n</div>\n";

		// h4
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_para('Heading 4', 'h4');
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_text("Font Size", "font_size_h4", "font_size_h4", $data['font_size_h4'], $font_size_options);
		echo form_text("Line Spacing", "font_h4_height", "font_h4_height", $data['font_h4_height'], $font_size_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_colorpicker('Font Color', "font_color_h4", "font_color_h4", $data['font_color_h4'], $color_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_select('Font Styling', "font_decoration_h4", "font_decoration_h4", $text_decoration, $data['font_decoration_h4'], $color_options);
		echo "</div>\n</div>\n";

		// h5
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_para('Heading 5', 'h5');
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_text("Font Size", "font_size_h5", "font_size_h5", $data['font_size_h5'], $font_size_options);
		echo form_text("Line Spacing", "font_h5_height", "font_h5_height", $data['font_h5_height'], $font_size_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_colorpicker('Font Color', "font_color_h5", "font_color_h5", $data['font_color_h5'], $color_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_select('Font Styling', "font_decoration_h5", "font_decoration_h5", $text_decoration, $data['font_decoration_h5'], $color_options);
		echo "</div>\n</div>\n";

		// h6
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_para('Heading 6', 'h6');
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_text("Font Size", "font_size_h6", "font_size_h6", $data['font_size_h6'], $font_size_options);
		echo form_text("Line Spacing", "font_h6_height", "font_h6_height", $data['font_h6_height'], $font_size_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_colorpicker('Font Color', "font_color_h6", "font_color_h6", $data['font_color_h6'], $color_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_select('Font Styling', "font_decoration_h6", "font_decoration_h6", $text_decoration, $data['font_decoration_h6'], $color_options);
		echo "</div>\n</div>\n";

		echo form_para("Link Color Settings", 'link_settings');
		echo "<hr>\n";
		// link
		$text_decoration = array('Normal', 'Bold', 'Underlined', 'Bold and Underlined');
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_para('Link Settings', 'link');
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_colorpicker('Link Base Color', "link_color", "link_color", $data['link_color'], $color_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_colorpicker('Link Hover Color', "link_hover_color", "link_hover_color", $data['link_hover_color'], $color_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_select('Link Hover Effects', "link_hover_effect", "link_hover_effect", $text_decoration, $data['link_hover_effect'], $color_options);
		echo "</div>\n</div>\n";

		echo form_para("Code Font", 'code_settings');
		echo "<hr>\n";
		// code
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_para('Code Font Settings', 'link');
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_colorpicker('Code Base Color', "code_color", "code_color", $data['code_color'], $color_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_colorpicker('Background Color', "code_bgcolor", "code_bgcolor", $data['code_bgcolor'], $color_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo "</div>\n</div>\n";


		// blockquote
		echo form_para("Blockquote Font", 'quote_settings');
		echo "<hr>\n";
		echo "<div class='row'>\n";
		echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_para('Blockquote', 'blockquote');
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_text("Font Size", "font_size_quote", "font_size_quote", $data['font_size_quote'], $font_size_options);
		echo form_text("Line Spacing", "font_quote_height", "font_quote_height", $data['font_quote_height'], $font_size_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_colorpicker('Font Color', "font_color_quote", "font_color_quote", $data['font_color_quote'], $color_options);
		echo "</div>\n<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3'>\n";
		echo form_select('Font Styling', "font_decoration_quote", "font_decoration_quote", $text_decoration, $data['font_decoration_quote'], $color_options);
		echo "</div>\n</div>\n";


	}


	public function atomize() {

	}

	public function add_panel() {

	}

	/* Returns list of google_fonts */
	function google_font() {
		$google_font = array(
			"Arial" => "Arial",
			"Lucida" => "Lucida",
			"Trebuchet" => "Trebuchet",
			"Geneva" => "Geneva",
			"ABeeZee" => "ABeeZee",
			"Abel" => "Abel",
			"Abril Fatface" => "Abril+Fatface",
			"Aclonica" => "Aclonica",
			"Acme" => "Acme",
			"Actor" => "Actor",
			"Adamina" => "Adamina",
			"Advent Pro" => "Advent+Pro",
			"Aguafina Script" => "Aguafina+Script",
			"Akronim" => "Akronim",
			"Aladin" => "Aladin",
			"Aldrich" => "Aldrich",
			"Alegreya" => "Alegreya",
			"Alegreya SC" => "Alegreya+SC",
			"Alex Brush" => "Alex+Brush",
			"Alfa Slab One" => "Alfa+Slab+One",
			"Alice" => "Alice",
			"Alike" => "Alike",
			"Alike Angular" => "Alike+Angular",
			"Allan" => "Allan",
			"Allerta" => "Allerta",
			"Allerta Stencil" => "Allerta+Stencil",
			"Allura" => "Allura",
			"Almendra" => "Almendra",
			"Almendra Display" => "Almendra+Display",
			"Almendra SC" => "Almendra+SC",
			"Amarante" => "Amarante",
			"Amaranth" => "Amaranth",
			"Amatic SC" => "Amatic+SC",
			"Amethysta" => "Amethysta",
			"Anaheim" => "Anaheim",
			"Andada" => "Andada",
			"Andika" => "Andika",
			"Angkor" => "Angkor",
			"Annie Use Your Telescope" => "Annie+Use+Your+Telescope",
			"Anonymous Pro" => "Anonymous+Pro",
			"Antic" => "Antic",
			"Antic Didone" => "Antic+Didone",
			"Antic Slab" => "Antic+Slab",
			"Anton" => "Anton",
			"Arapey" => "Arapey",
			"Arbutus" => "Arbutus",
			"Arbutus Slab" => "Arbutus+Slab",
			"Architects Daughter" => "Architects+Daughter",
			"Archivo Black" => "Archivo+Black",
			"Archivo Narrow" => "Archivo+Narrow",
			"Arimo" => "Arimo",
			"Arizonia" => "Arizonia",
			"Armata" => "Armata",
			"Artifika" => "Artifika",
			"Arvo" => "Arvo",
			"Asap" => "Asap",
			"Asset" => "Asset",
			"Astloch" => "Astloch",
			"Asul" => "Asul",
			"Atomic Age" => "Atomic+Age",
			"Aubrey" => "Aubrey",
			"Audiowide" => "Audiowide",
			"Autour One" => "Autour+One",
			"Average" => "Average",
			"Average Sans" => "Average+Sans",
			"Averia Gruesa Libre" => "Averia+Gruesa+Libre",
			"Averia Libre" => "Averia+Libre",
			"Averia Sans Libre" => "Averia+Sans+Libre",
			"Averia Serif Libre" => "Averia+Serif+Libre",
			"Bad Script" => "Bad+Script",
			"Balthazar" => "Balthazar",
			"Bangers" => "Bangers",
			"Basic" => "Basic",
			"Battambang" => "Battambang",
			"Baumans" => "Baumans",
			"Bayon" => "Bayon",
			"Belgrano" => "Belgrano",
			"Belleza" => "Belleza",
			"BenchNine" => "BenchNine",
			"Bentham" => "Bentham",
			"Berkshire Swash" => "Berkshire+Swash",
			"Bevan" => "Bevan",
			"Bigelow Rules" => "Bigelow+Rules",
			"Bigshot One" => "Bigshot+One",
			"Bilbo" => "Bilbo",
			"Bilbo Swash Caps" => "Bilbo+Swash+Caps",
			"Bitter" => "Bitter",
			"Black Ops One" => "Black+Ops+One",
			"Bokor" => "Bokor",
			"Bonbon" => "Bonbon",
			"Boogaloo" => "Boogaloo",
			"Bowlby One" => "Bowlby+One",
			"Bowlby One SC" => "Bowlby+One+SC",
			"Brawler" => "Brawler",
			"Bree Serif" => "Bree+Serif",
			"Bubblegum Sans" => "Bubblegum+Sans",
			"Bubbler One" => "Bubbler+One",
			"Buda" => "Buda",
			"Buenard" => "Buenard",
			"Butcherman" => "Butcherman",
			"Butterfly Kids" => "Butterfly+Kids",
			"Cabin" => "Cabin",
			"Cabin Condensed" => "Cabin+Condensed",
			"Cabin Sketch" => "Cabin+Sketch",
			"Caesar Dressing" => "Caesar+Dressing",
			"Cagliostro" => "Cagliostro",
			"Calligraffitti" => "Calligraffitti",
			"Cambo" => "Cambo",
			"Candal" => "Candal",
			"Cantarell" => "Cantarell",
			"Cantata One" => "Cantata+One",
			"Cantora One" => "Cantora+One",
			"Capriola" => "Capriola",
			"Cardo" => "Cardo",
			"Carme" => "Carme",
			"Carrois Gothic" => "Carrois+Gothic",
			"Carrois Gothic SC" => "Carrois+Gothic+SC",
			"Carter One" => "Carter+One",
			"Caudex" => "Caudex",
			"Cedarville Cursive" => "Cedarville+Cursive",
			"Ceviche One" => "Ceviche+One",
			"Changa One" => "Changa+One",
			"Chango" => "Chango",
			"Chau Philomene One" => "Chau+Philomene+One",
			"Chela One" => "Chela+One",
			"Chelsea Market" => "Chelsea+Market",
			"Chenla" => "Chenla",
			"Cherry Cream Soda" => "Cherry+Cream+Soda",
			"Cherry Swash" => "Cherry+Swash",
			"Chewy" => "Chewy",
			"Chicle" => "Chicle",
			"Chivo" => "Chivo",
			"Cinzel" => "Cinzel",
			"Cinzel Decorative" => "Cinzel+Decorative",
			"Clicker Script" => "Clicker+Script",
			"Coda" => "Coda",
			"Coda Caption" => "Coda+Caption",
			"Codystar" => "Codystar",
			"Combo" => "Combo",
			"Comfortaa" => "Comfortaa",
			"Coming Soon" => "Coming+Soon",
			"Concert One" => "Concert+One",
			"Condiment" => "Condiment",
			"Content" => "Content",
			"Contrail One" => "Contrail+One",
			"Convergence" => "Convergence",
			"Cookie" => "Cookie",
			"Copse" => "Copse",
			"Corben" => "Corben",
			"Courgette" => "Courgette",
			"Cousine" => "Cousine",
			"Coustard" => "Coustard",
			"Covered By Your Grace" => "Covered+By+Your+Grace",
			"Crafty Girls" => "Crafty+Girls",
			"Creepster" => "Creepster",
			"Crete Round" => "Crete+Round",
			"Crimson Text" => "Crimson+Text",
			"Croissant One" => "Croissant+One",
			"Crushed" => "Crushed",
			"Cuprum" => "Cuprum",
			"Cutive" => "Cutive",
			"Cutive Mono" => "Cutive+Mono",
			"Damion" => "Damion",
			"Dancing Script" => "Dancing+Script",
			"Dangrek" => "Dangrek",
			"Dawning of a New Day" => "Dawning+of+a+New+Day",
			"Days One" => "Days+One",
			"Delius" => "Delius",
			"Delius Swash Caps" => "Delius+Swash+Caps",
			"Delius Unicase" => "Delius+Unicase",
			"Della Respira" => "Della+Respira",
			"Denk One" => "Denk+One",
			"Devonshire" => "Devonshire",
			"Didact Gothic" => "Didact+Gothic",
			"Diplomata" => "Diplomata",
			"Diplomata SC" => "Diplomata+SC",
			"Domine" => "Domine",
			"Donegal One" => "Donegal+One",
			"Doppio One" => "Doppio+One",
			"Dorsa" => "Dorsa",
			"Dosis" => "Dosis",
			"Dr Sugiyama" => "Dr+Sugiyama",
			"Droid Sans" => "Droid+Sans",
			"Droid Sans Mono" => "Droid+Sans+Mono",
			"Droid Serif" => "Droid+Serif",
			"Duru Sans" => "Duru+Sans",
			"Dynalight" => "Dynalight",
			"EB Garamond" => "EB+Garamond",
			"Eagle Lake" => "Eagle+Lake",
			"Eater" => "Eater",
			"Economica" => "Economica",
			"Electrolize" => "Electrolize",
			"Elsie" => "Elsie",
			"Elsie Swash Caps" => "Elsie+Swash+Caps",
			"Emblema One" => "Emblema+One",
			"Emilys Candy" => "Emilys+Candy",
			"Engagement" => "Engagement",
			"Englebert" => "Englebert",
			"Enriqueta" => "Enriqueta",
			"Erica One" => "Erica+One",
			"Esteban" => "Esteban",
			"Euphoria Script" => "Euphoria+Script",
			"Ewert" => "Ewert",
			"Exo" => "Exo",
			"Expletus Sans" => "Expletus+Sans",
			"Fanwood Text" => "Fanwood+Text",
			"Fascinate" => "Fascinate",
			"Fascinate Inline" => "Fascinate+Inline",
			"Faster One" => "Faster+One",
			"Fasthand" => "Fasthand",
			"Federant" => "Federant",
			"Federo" => "Federo",
			"Felipa" => "Felipa",
			"Fenix" => "Fenix",
			"Finger Paint" => "Finger+Paint",
			"Fjalla One" => "Fjalla+One",
			"Fjord One" => "Fjord+One",
			"Flamenco" => "Flamenco",
			"Flavors" => "Flavors",
			"Fondamento" => "Fondamento",
			"Fontdiner Swanky" => "Fontdiner+Swanky",
			"Forum" => "Forum",
			"Francois One" => "Francois+One",
			"Freckle Face" => "Freckle+Face",
			"Fredericka the Great" => "Fredericka+the+Great",
			"Fredoka One" => "Fredoka+One",
			"Freehand" => "Freehand",
			"Fresca" => "Fresca",
			"Frijole" => "Frijole",
			"Fruktur" => "Fruktur",
			"Fugaz One" => "Fugaz+One",
			"GFS Didot" => "GFS+Didot",
			"GFS Neohellenic" => "GFS+Neohellenic",
			"Gabriela" => "Gabriela",
			"Gafata" => "Gafata",
			"Galdeano" => "Galdeano",
			"Galindo" => "Galindo",
			"Gentium Basic" => "Gentium+Basic",
			"Gentium Book Basic" => "Gentium+Book+Basic",
			"Geo" => "Geo",
			"Geostar" => "Geostar",
			"Geostar Fill" => "Geostar+Fill",
			"Germania One" => "Germania+One",
			"Gilda Display" => "Gilda+Display",
			"Give You Glory" => "Give+You+Glory",
			"Glass Antiqua" => "Glass+Antiqua",
			"Glegoo" => "Glegoo",
			"Gloria Hallelujah" => "Gloria+Hallelujah",
			"Goblin One" => "Goblin+One",
			"Gochi Hand" => "Gochi+Hand",
			"Gorditas" => "Gorditas",
			"Goudy Bookletter 1911" => "Goudy+Bookletter+1911",
			"Graduate" => "Graduate",
			"Grand Hotel" => "Grand+Hotel",
			"Gravitas One" => "Gravitas+One",
			"Great Vibes" => "Great+Vibes",
			"Griffy" => "Griffy",
			"Gruppo" => "Gruppo",
			"Gudea" => "Gudea",
			"Habibi" => "Habibi",
			"Hammersmith One" => "Hammersmith+One",
			"Hanalei" => "Hanalei",
			"Hanalei Fill" => "Hanalei+Fill",
			"Handlee" => "Handlee",
			"Hanuman" => "Hanuman",
			"Happy Monkey" => "Happy+Monkey",
			"Headland One" => "Headland+One",
			"Henny Penny" => "Henny+Penny",
			"Herr Von Muellerhoff" => "Herr+Von+Muellerhoff",
			"Holtwood One SC" => "Holtwood+One+SC",
			"Homemade Apple" => "Homemade+Apple",
			"Homenaje" => "Homenaje",
			"IM Fell DW Pica" => "IM+Fell+DW+Pica",
			"IM Fell DW Pica SC" => "IM+Fell+DW+Pica+SC",
			"IM Fell Double Pica" => "IM+Fell+Double+Pica",
			"IM Fell Double Pica SC" => "IM+Fell+Double+Pica+SC",
			"IM Fell English" => "IM+Fell+English",
			"IM Fell English SC" => "IM+Fell+English+SC",
			"IM Fell French Canon" => "IM+Fell+French+Canon",
			"IM Fell French Canon SC" => "IM+Fell+French+Canon+SC",
			"IM Fell Great Primer" => "IM+Fell+Great+Primer",
			"IM Fell Great Primer SC" => "IM+Fell+Great+Primer+SC",
			"Iceberg" => "Iceberg",
			"Iceland" => "Iceland",
			"Imprima" => "Imprima",
			"Inconsolata" => "Inconsolata",
			"Inder" => "Inder",
			"Indie Flower" => "Indie+Flower",
			"Inika" => "Inika",
			"Irish Grover" => "Irish+Grover",
			"Istok Web" => "Istok+Web",
			"Italiana" => "Italiana",
			"Italianno" => "Italianno",
			"Jacques Francois" => "Jacques+Francois",
			"Jacques Francois Shadow" => "Jacques+Francois+Shadow",
			"Jim Nightshade" => "Jim+Nightshade",
			"Jockey One" => "Jockey+One",
			"Jolly Lodger" => "Jolly+Lodger",
			"Josefin Sans" => "Josefin+Sans",
			"Josefin Slab" => "Josefin+Slab",
			"Joti One" => "Joti+One",
			"Judson" => "Judson",
			"Julee" => "Julee",
			"Julius Sans One" => "Julius+Sans+One",
			"Junge" => "Junge",
			"Jura" => "Jura",
			"Just Another Hand" => "Just+Another+Hand",
			"Just Me Again Down Here" => "Just+Me+Again+Down+Here",
			"Kameron" => "Kameron",
			"Karla" => "Karla",
			"Kaushan Script" => "Kaushan+Script",
			"Kavoon" => "Kavoon",
			"Keania One" => "Keania+One",
			"Kelly Slab" => "Kelly+Slab",
			"Kenia" => "Kenia",
			"Khmer" => "Khmer",
			"Kite One" => "Kite+One",
			"Knewave" => "Knewave",
			"Kotta One" => "Kotta+One",
			"Koulen" => "Koulen",
			"Kranky" => "Kranky",
			"Kreon" => "Kreon",
			"Kristi" => "Kristi",
			"Krona One" => "Krona+One",
			"La Belle Aurore" => "La+Belle+Aurore",
			"Lancelot" => "Lancelot",
			"Lato" => "Lato",
			"League Script" => "League+Script",
			"Leckerli One" => "Leckerli+One",
			"Ledger" => "Ledger",
			"Lekton" => "Lekton",
			"Lemon" => "Lemon",
			"Libre Baskerville" => "Libre+Baskerville",
			"Life Savers" => "Life+Savers",
			"Lilita One" => "Lilita+One",
			"Limelight" => "Limelight",
			"Linden Hill" => "Linden+Hill",
			"Lobster" => "Lobster",
			"Lobster Two" => "Lobster+Two",
			"Londrina Outline" => "Londrina+Outline",
			"Londrina Shadow" => "Londrina+Shadow",
			"Londrina Sketch" => "Londrina+Sketch",
			"Londrina Solid" => "Londrina+Solid",
			"Lora" => "Lora",
			"Love Ya Like A Sister" => "Love+Ya+Like+A+Sister",
			"Loved by the King" => "Loved+by+the+King",
			"Lovers Quarrel" => "Lovers+Quarrel",
			"Luckiest Guy" => "Luckiest+Guy",
			"Lusitana" => "Lusitana",
			"Lustria" => "Lustria",
			"Macondo" => "Macondo",
			"Macondo Swash Caps" => "Macondo+Swash+Caps",
			"Magra" => "Magra",
			"Maiden Orange" => "Maiden+Orange",
			"Mako" => "Mako",
			"Marcellus" => "Marcellus",
			"Marcellus SC" => "Marcellus+SC",
			"Marck Script" => "Marck+Script",
			"Margarine" => "Margarine",
			"Marko One" => "Marko+One",
			"Marmelad" => "Marmelad",
			"Marvel" => "Marvel",
			"Mate" => "Mate",
			"Mate SC" => "Mate+SC",
			"Maven Pro" => "Maven+Pro",
			"McLaren" => "McLaren",
			"Meddon" => "Meddon",
			"MedievalSharp" => "MedievalSharp",
			"Medula One" => "Medula+One",
			"Megrim" => "Megrim",
			"Meie Script" => "Meie+Script",
			"Merienda" => "Merienda",
			"Merienda One" => "Merienda+One",
			"Merriweather" => "Merriweather",
			"Merriweather Sans" => "Merriweather+Sans",
			"Metal" => "Metal",
			"Metal Mania" => "Metal+Mania",
			"Metamorphous" => "Metamorphous",
			"Metrophobic" => "Metrophobic",
			"Michroma" => "Michroma",
			"Milonga" => "Milonga",
			"Miltonian" => "Miltonian",
			"Miltonian Tattoo" => "Miltonian+Tattoo",
			"Miniver" => "Miniver",
			"Miss Fajardose" => "Miss+Fajardose",
			"Modern Antiqua" => "Modern+Antiqua",
			"Molengo" => "Molengo",
			"Molle" => "Molle",
			"Monda" => "Monda",
			"Monofett" => "Monofett",
			"Monoton" => "Monoton",
			"Monsieur La Doulaise" => "Monsieur+La+Doulaise",
			"Montaga" => "Montaga",
			"Montez" => "Montez",
			"Montserrat" => "Montserrat",
			"Montserrat Alternates" => "Montserrat+Alternates",
			"Montserrat Subrayada" => "Montserrat+Subrayada",
			"Moul" => "Moul",
			"Moulpali" => "Moulpali",
			"Mountains of Christmas" => "Mountains+of+Christmas",
			"Mouse Memoirs" => "Mouse+Memoirs",
			"Mr Bedfort" => "Mr+Bedfort",
			"Mr Dafoe" => "Mr+Dafoe",
			"Mr De Haviland" => "Mr+De+Haviland",
			"Mrs Saint Delafield" => "Mrs+Saint+Delafield",
			"Mrs Sheppards" => "Mrs+Sheppards",
			"Muli" => "Muli",
			"Mystery Quest" => "Mystery+Quest",
			"Neucha" => "Neucha",
			"Neuton" => "Neuton",
			"New Rocker" => "New+Rocker",
			"News Cycle" => "News+Cycle",
			"Niconne" => "Niconne",
			"Nixie One" => "Nixie+One",
			"Nobile" => "Nobile",
			"Nokora" => "Nokora",
			"Norican" => "Norican",
			"Nosifer" => "Nosifer",
			"Nothing You Could Do" => "Nothing+You+Could+Do",
			"Noticia Text" => "Noticia+Text",
			"Nova Cut" => "Nova+Cut",
			"Nova Flat" => "Nova+Flat",
			"Nova Mono" => "Nova+Mono",
			"Nova Oval" => "Nova+Oval",
			"Nova Round" => "Nova+Round",
			"Nova Script" => "Nova+Script",
			"Nova Slim" => "Nova+Slim",
			"Nova Square" => "Nova+Square",
			"Numans" => "Numans",
			"Nunito" => "Nunito",
			"Odor Mean Chey" => "Odor+Mean+Chey",
			"Offside" => "Offside",
			"Old Standard TT" => "Old+Standard+TT",
			"Oldenburg" => "Oldenburg",
			"Oleo Script" => "Oleo+Script",
			"Oleo Script Swash Caps" => "Oleo+Script+Swash+Caps",
			"Open Sans" => "Open+Sans",
			"Open Sans Condensed" => "Open+Sans+Condensed",
			"Oranienbaum" => "Oranienbaum",
			"Orbitron" => "Orbitron",
			"Oregano" => "Oregano",
			"Orienta" => "Orienta",
			"Original Surfer" => "Original+Surfer",
			"Oswald" => "Oswald",
			"Over the Rainbow" => "Over+the+Rainbow",
			"Overlock" => "Overlock",
			"Overlock SC" => "Overlock+SC",
			"Ovo" => "Ovo",
			"Oxygen" => "Oxygen",
			"Oxygen Mono" => "Oxygen+Mono",
			"PT Mono" => "PT+Mono",
			"PT Sans" => "PT+Sans",
			"PT Sans Caption" => "PT+Sans+Caption",
			"PT Sans Narrow" => "PT+Sans+Narrow",
			"PT Serif" => "PT+Serif",
			"PT Serif Caption" => "PT+Serif+Caption",
			"Pacifico" => "Pacifico",
			"Paprika" => "Paprika",
			"Parisienne" => "Parisienne",
			"Passero One" => "Passero+One",
			"Passion One" => "Passion+One",
			"Patrick Hand" => "Patrick+Hand",
			"Patrick Hand SC" => "Patrick+Hand+SC",
			"Patua One" => "Patua+One",
			"Paytone One" => "Paytone+One",
			"Peralta" => "Peralta",
			"Permanent Marker" => "Permanent+Marker",
			"Petit Formal Script" => "Petit+Formal+Script",
			"Petrona" => "Petrona",
			"Philosopher" => "Philosopher",
			"Piedra" => "Piedra",
			"Pinyon Script" => "Pinyon+Script",
			"Pirata One" => "Pirata+One",
			"Plaster" => "Plaster",
			"Play" => "Play",
			"Playball" => "Playball",
			"Playfair Display" => "Playfair+Display",
			"Playfair Display SC" => "Playfair+Display+SC",
			"Podkova" => "Podkova",
			"Poiret One" => "Poiret+One",
			"Poller One" => "Poller+One",
			"Poly" => "Poly",
			"Pompiere" => "Pompiere",
			"Pontano Sans" => "Pontano+Sans",
			"Port Lligat Sans" => "Port+Lligat+Sans",
			"Port Lligat Slab" => "Port+Lligat+Slab",
			"Prata" => "Prata",
			"Preahvihear" => "Preahvihear",
			"Press Start 2P" => "Press+Start+2P",
			"Princess Sofia" => "Princess+Sofia",
			"Prociono" => "Prociono",
			"Prosto One" => "Prosto+One",
			"Puritan" => "Puritan",
			"Purple Purse" => "Purple+Purse",
			"Quando" => "Quando",
			"Quantico" => "Quantico",
			"Quattrocento" => "Quattrocento",
			"Quattrocento Sans" => "Quattrocento+Sans",
			"Questrial" => "Questrial",
			"Quicksand" => "Quicksand",
			"Quintessential" => "Quintessential",
			"Qwigley" => "Qwigley",
			"Racing Sans One" => "Racing+Sans+One",
			"Radley" => "Radley",
			"Raleway" => "Raleway",
			"Raleway Dots" => "Raleway+Dots",
			"Rambla" => "Rambla",
			"Rammetto One" => "Rammetto+One",
			"Ranchers" => "Ranchers",
			"Rancho" => "Rancho",
			"Rationale" => "Rationale",
			"Redressed" => "Redressed",
			"Reenie Beanie" => "Reenie+Beanie",
			"Revalia" => "Revalia",
			"Ribeye" => "Ribeye",
			"Ribeye Marrow" => "Ribeye+Marrow",
			"Righteous" => "Righteous",
			"Risque" => "Risque",
			"Roboto" => "Roboto",
			"Roboto Condensed" => "Roboto+Condensed",
			"Rochester" => "Rochester",
			"Rock Salt" => "Rock+Salt",
			"Rokkitt" => "Rokkitt",
			"Romanesco" => "Romanesco",
			"Ropa Sans" => "Ropa+Sans",
			"Rosario" => "Rosario",
			"Rosarivo" => "Rosarivo",
			"Rouge Script" => "Rouge+Script",
			"Ruda" => "Ruda",
			"Rufina" => "Rufina",
			"Ruge Boogie" => "Ruge+Boogie",
			"Ruluko" => "Ruluko",
			"Rum Raisin" => "Rum+Raisin",
			"Ruslan Display" => "Ruslan+Display",
			"Russo One" => "Russo+One",
			"Ruthie" => "Ruthie",
			"Rye" => "Rye",
			"Sacramento" => "Sacramento",
			"Sail" => "Sail",
			"Salsa" => "Salsa",
			"Sanchez" => "Sanchez",
			"Sancreek" => "Sancreek",
			"Sansita One" => "Sansita+One",
			"Sarina" => "Sarina",
			"Satisfy" => "Satisfy",
			"Scada" => "Scada",
			"Schoolbell" => "Schoolbell",
			"Seaweed Script" => "Seaweed+Script",
			"Sevillana" => "Sevillana",
			"Seymour One" => "Seymour+One",
			"Shadows Into Light" => "Shadows+Into+Light",
			"Shadows Into Light Two" => "Shadows+Into+Light+Two",
			"Shanti" => "Shanti",
			"Share" => "Share",
			"Share Tech" => "Share+Tech",
			"Share Tech Mono" => "Share+Tech+Mono",
			"Shojumaru" => "Shojumaru",
			"Short Stack" => "Short+Stack",
			"Siemreap" => "Siemreap",
			"Sigmar One" => "Sigmar+One",
			"Signika" => "Signika",
			"Signika Negative" => "Signika+Negative",
			"Simonetta" => "Simonetta",
			"Sintony" => "Sintony",
			"Sirin Stencil" => "Sirin+Stencil",
			"Six Caps" => "Six+Caps",
			"Skranji" => "Skranji",
			"Slackey" => "Slackey",
			"Smokum" => "Smokum",
			"Smythe" => "Smythe",
			"Sniglet" => "Sniglet",
			"Snippet" => "Snippet",
			"Snowburst One" => "Snowburst+One",
			"Sofadi One" => "Sofadi+One",
			"Sofia" => "Sofia",
			"Sonsie One" => "Sonsie+One",
			"Sorts Mill Goudy" => "Sorts+Mill+Goudy",
			"Source Code Pro" => "Source+Code+Pro",
			"Source Sans Pro" => "Source+Sans+Pro",
			"Special Elite" => "Special+Elite",
			"Spicy Rice" => "Spicy+Rice",
			"Spinnaker" => "Spinnaker",
			"Spirax" => "Spirax",
			"Squada One" => "Squada+One",
			"Stalemate" => "Stalemate",
			"Stalinist One" => "Stalinist+One",
			"Stardos Stencil" => "Stardos+Stencil",
			"Stint Ultra Condensed" => "Stint+Ultra+Condensed",
			"Stint Ultra Expanded" => "Stint+Ultra+Expanded",
			"Stoke" => "Stoke",
			"Strait" => "Strait",
			"Sue Ellen Francisco" => "Sue+Ellen+Francisco",
			"Sunshiney" => "Sunshiney",
			"Supermercado One" => "Supermercado+One",
			"Suwannaphum" => "Suwannaphum",
			"Swanky and Moo Moo" => "Swanky+and+Moo+Moo",
			"Syncopate" => "Syncopate",
			"Tangerine" => "Tangerine",
			"Taprom" => "Taprom",
			"Tauri" => "Tauri",
			"Telex" => "Telex",
			"Tenor Sans" => "Tenor+Sans",
			"Text Me One" => "Text+Me+One",
			"The Girl Next Door" => "The+Girl+Next+Door",
			"Tienne" => "Tienne",
			"Tinos" => "Tinos",
			"Titan One" => "Titan+One",
			"Titillium Web" => "Titillium+Web",
			"Trade Winds" => "Trade+Winds",
			"Trocchi" => "Trocchi",
			"Trochut" => "Trochut",
			"Trykker" => "Trykker",
			"Tulpen One" => "Tulpen+One",
			"Ubuntu" => "Ubuntu",
			"Ubuntu Condensed" => "Ubuntu+Condensed",
			"Ubuntu Mono" => "Ubuntu+Mono",
			"Ultra" => "Ultra",
			"Uncial Antiqua" => "Uncial+Antiqua",
			"Underdog" => "Underdog",
			"Unica One" => "Unica+One",
			"UnifrakturCook" => "UnifrakturCook",
			"UnifrakturMaguntia" => "UnifrakturMaguntia",
			"Unkempt" => "Unkempt",
			"Unlock" => "Unlock",
			"Unna" => "Unna",
			"VT323" => "VT323",
			"Vampiro One" => "Vampiro+One",
			"Varela" => "Varela",
			"Varela Round" => "Varela+Round",
			"Vast Shadow" => "Vast+Shadow",
			"Vibur" => "Vibur",
			"Vidaloka" => "Vidaloka",
			"Viga" => "Viga",
			"Voces" => "Voces",
			"Volkhov" => "Volkhov",
			"Vollkorn" => "Vollkorn",
			"Voltaire" => "Voltaire",
			"Waiting for the Sunrise" => "Waiting+for+the+Sunrise",
			"Wallpoet" => "Wallpoet",
			"Walter Turncoat" => "Walter+Turncoat",
			"Warnes" => "Warnes",
			"Wellfleet" => "Wellfleet",
			"Wendy One" => "Wendy+One",
			"Wire One" => "Wire+One",
			"Yanone Kaffeesatz" => "Yanone+Kaffeesatz",
			"Yellowtail" => "Yellowtail",
			"Yeseva One" => "Yeseva+One",
			"Yesteryear" => "Yesteryear",
			"Zeyada" => "Zeyada"
		);
		//api at google : <link href=http://fonts.googleapis.com/css?family=Signika Negative rel=stylesheet type=text/css>
		return $google_font;

	}

	/* Returns list of common base fonts */
	function base_font() {
		// OS Font Defaults
		return array(
			"Arial" => "Arial",
			"Avant Garde" => "Avant+Garde",
			"Cambria" => "Cambria",
			"Copse" => "Copse",
			"Garamond" => "Garamond",
			"Georgia" => "Georgia",
			"Heofler_Text" => "Hoefler+Text",
			"Helvetica" => "Helvetica",
			"Helvetica Neue" => "Helvetica+Neue",
			"Tahoma" => "Tahoma",
			"Times New Roman" => "Times+New+Roman",
			"Times" => "Times",
			"Lucida Grande" => "Lucida+Grande",
			"Lucida Sans Unicode" => "Lucida+Sans+Unicode",
			"Verdana" => "Verdana",
			"sans-serif" => "sans-serif",
			"serif" => "serif"
		);

		//print_p($os_faces);
	}


}
?>