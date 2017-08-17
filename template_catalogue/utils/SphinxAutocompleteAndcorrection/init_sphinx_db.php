#!/usr/bin/php
<?php
set_include_path('.:/usr/share/pear:/usr/share/php:/www/'.strtolower('#MainProject').':/www/'.strtolower('#MainProject').'/scripts');
require_once 'utils/SphinxAutocompleteAndcorrection/sphinx_keyword_insertion.php';

insert_keywords_docs_suggest();

?>