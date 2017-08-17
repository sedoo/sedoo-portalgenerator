<?php
require_once ('fpdf17/fpdf.php');
class projectPDF extends FPDF {
	var $text_indent = 0;
	var $titre;
	function projectPdf($titre = 'Instrument Form') {
		parent::__construct ();
		$this->titre = $titre;
		$this->AddPage ();
		$this->addTitre ( $titre );
		$this->AliasNbPages ();
	}
	function addTitre($titre) {
		$this->SetLineWidth ( 0.2 );
		$this->SetFont ( 'Arial', 'B', 16 );
		$this->Multicell ( 0, 10, $titre, 1, 'C' );
	}
	
	// Pied de page
	function Footer() {
		$this->SetY ( - 10 );
		$this->SetFont ( 'Arial', '', 8 );
		// Numéro de page
		$this->Cell ( 30, 6, 'Database', 0, 0, 'L' );
		$this->Cell ( 120, 6, $this->titre, 0, 0, 'C' );
		$this->Cell ( 0, 6, 'Page ' . $this->PageNo () . '/{nb}', 0, 0, 'R' );
	}
	function addSection($titre) {
		if ($this->getY () > 230)
			$this->AddPage ();
		$this->ln ();
		$this->ln ();
		$this->SetLineWidth ( 0.2 );
		$this->setFont ( 'Arial', 'B', 12 );
		$this->Multicell ( 0, 6, utf8_decode ( $titre ), 'B' );
		$this->text_indent = 5;
		// $this->ln();
	}
	function newLine() {
		$this->ln ();
	}
	function indent($i) {
		if ($i > 0) {
			$posX = $this->getX ();
			$this->setX ( $posX + $i );
		}
	}
	function addImage($image) {
		$this->ln ();
		$this->Image ( str_replace ( ' ', '%20', $image ), null, null, 190 );
	}
	function addSousSection($titre, $data = array(), $attr = null) {
		if (isset ( $data )) {
			
			if ($this->getY () > 250)
				$this->AddPage ();
			
			$this->ln ();
			$this->setFont ( 'Arial', '', 12 );
			$this->SetLineWidth ( 0.1 );
			$this->indent ( 5 );
			$this->Multicell ( 0, 6, utf8_decode ( $titre ), 'B' );
			$this->text_indent = 9;
			// $this->ln();
			
			if (is_array ( $data ))
				$this->addList ( $data, $attr );
			else if (is_string ( $data ))
				$this->addText ( $data );
		}
	}
	function addText($str) {
		$this->setFont ( 'Arial', '', 10 );
		$this->indent ( $this->text_indent );
		$str = utf8_decode ( $str );
		$this->Multicell ( 0, 6, $str );
	}
	function addList($list, $attr = null) {
		foreach ( $list as $item ) {
			if (isset ( $attr ))
				$this->addText ( "$attr: " . $item->$attr );
			else
				$this->addText ( $item );
		}
	}
	function addLabelList($label, $list, $attr = null) {
		$str = '';
		foreach ( $list as $item ) {
			if (isset ( $attr ))
				$str .= "\n" . $item->$attr;
			else
				$str .= "\n" . $item;
		}
		$this->addLabelValue ( $label, substr ( $str, 1 ) );
	}
	function addLabelValue($label, $value, $hideIfEmpty = true) {
		$value = trim ( $value );
		if ((isset ( $value ) && ! empty ( $value )) || ! $hideIfEmpty) {
			$this->setFont ( 'Arial', '', 10 );
			$this->indent ( $this->text_indent );
			$this->Cell ( 60, 6, utf8_decode ( $label ) . ': ' );
			$this->Multicell ( 0, 6, utf8_decode ( $value ) );
		}
	}
}

?>
