<?php
session_set_save_handler("sopen", "sclose", "sread", "swrite", "sdestroy", "sgc");
?>