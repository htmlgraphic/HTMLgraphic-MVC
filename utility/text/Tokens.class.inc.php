<?php

/*
  Tokens or also called Stop Words. Not to be confused with 'tokin' or used as a racial slur
 */

class Tokens
{

  //get tokens from pregen, if they don't exist regenerate them and return the tokens
  static function pull()
  {
    $tokenGen = new TokenPregen();
    $tokens = $tokenGen->getWords();
    if (count($tokens) < 2)
    {
      $tokens = $tokenGen->regenerate();
      $tokens = $tokenGen->save();
      $tokens = $tokenGen->getWords();
    }

    //if all fails get them manually
    if (count($tokens) < 2)
    {
      $tokens = Tokens::getList();
    }
    return $tokens;
  }

  static function getList()
  {
    return $tokens = array(
"a", "a'", "abl", "about", "abov", "accord", "accordingli", "across", "actual", "after",
 "afterward", "again", "against", "ai", "aint", "all", "allow", "allow", "almost", "alon",
 "along", "alreadi", "also", "although", "alwai", "am", "among", "amongst", "an", "and",
 "anoth", "ani", "anybodi", "anyhow", "anyon", "anyth", "anywai", "anywai", "anywher",
 "apart", "appear", "appreci", "appropri", "ar", "ar", "arent", "around", "as", "asid",
 "ask", "ask", "associ", "at", "avail", "awai", "awfulli", "b", "be", "becam", "becaus",
 "becom", "becom", "becom", "been", "befor", "beforehand", "behind", "be", "believ", "below",
 "besid", "besid", "best", "better", "between", "beyond", "both", "brief", "but", "by",
 "c", "c'mon", "c'", "came", "can", "ca", "cannot", "cant", "caus", "caus", "certain",
 "certainli", "chang", "clearli", "cmon", "co", "com", "come", "come", "concern", "consequ",
 "consid", "consid", "contain", "contain", "contain", "correspond", "could", "could",
 "couldnt", "cours", "cs", "current", "d", "definit", "describ", "despit", "did", "did",
 "didnt", "differ", "do", "doe", "doe", "doesnt", "do", "do", "done", "dont", "down", "downward",
 "dure", "e", "each", "edu", "eg", "eight", "either", "els", "elsewher", "enough", "entir",
 "especi", "et", "etc", "even", "ever", "everi", "everybodi", "everyon", "everyth", "everywher",
 "ex", "exactli", "exampl", "except", "f", "far", "few", "fifth", "first", "five", "follow",
 "follow", "follow", "for", "form", "former", "formerli", "forth", "four", "from", "further",
 "furthermor", "g", "get", "get", "get", "given", "give", "go", "goe", "go", "gone", "got",
 "gotten", "greet", "h", "had", "had", "hadnt", "happen", "hardli", "ha", "ha", "hasnt", "have",
 "have", "havent", "have", "he", "he'", "hello", "help", "henc", "her", "here", "here'", "hereaft",
 "herebi", "herein", "here", "hereupon", "her", "herself", "he", "hi", "him", "himself", "hi", "hither",
 "hopefulli", "how", "howbeit", "howev", "i", "i", "i'll", "i'm", "i", "id", "ie", "if", "ignor",
 "ill", "im", "immedi", "in", "inasmuch", "inc", "inde", "indic", "indic", "indic", "inner",
 "insofar", "instead", "into", "inward", "is", "i", "isnt", "it", "it", "it'll", "it'", "itd",
 "itll", "it", "itself", "iv", "j", "just", "k", "keep", "keep", "kept", "know", "known", "know",
 "l", "last", "late", "later", "latter", "latterli", "least", "less", "lest", "let", "let'", "let",
 "like", "like", "like", "littl", "look", "look", "look", "ltd", "m", "mainli", "mani", "mai", "mayb",
 "me", "mean", "meanwhil", "mere", "might", "more", "moreov", "most", "mostli", "much", "must", "my",
 "myself", "n", "name", "name", "nbsp", "nd", "near", "nearli", "necessari", "need", "need", "neither",
 "never", "nevertheless", "new", "next", "nine", "no", "nobodi", "non", "none", "noon", "nor",
 "normal", "not", "noth", "novel", "now", "nowher", "o", "obvious", "of", "off", "often", "oh", "ok",
 "okai", "old", "on", "onc", "on", "on", "onli", "onto", "or", "other", "other", "otherwis", "ought",
 "our", "our", "ourselv", "out", "outsid", "over", "overal", "own", "p", "particular", "particularli",
 "per", "perhap", "place", "pleas", "plu", "possibl", "presum", "probabl", "provid", "q", "que",
 "quit", "qv", "r", "rather", "rd", "re", "realli", "reason", "regard", "regardless", "regard", "rel",
 "respect", "right", "s", "said", "same", "saw", "sai", "sai", "sai", "second", "secondli", "see",
 "see", "seem", "seem", "seem", "seem", "seen", "self", "selv", "sensibl", "sent", "seriou", "serious",
 "seven", "sever", "shall", "she", "should", "should", "shouldnt", "shouldn?t", "sinc", "six", "so",
 "some", "somebodi", "somehow", "someon", "someth", "sometim", "sometim", "somewhat", "somewher",
 "soon", "sorri", "specifi", "specifi", "specifi", "still", "sub", "such", "sup", "sure", "t", "t'",
 "take", "taken", "tell", "tend", "th", "than", "thank", "thank", "thanx", "that", "that'", "that",
 "the", "their", "their", "them", "themselv", "then", "thenc", "there", "there'", "thereaft", "therebi",
 "therefor", "therein", "there", "thereupon", "these", "thei", "thei", "they'll", "they'r", "thei", "theyd",
 "theyll", "theyr", "theyv", "think", "third", "thi", "thorough", "thoroughli", "those", "though",
 "three", "through", "throughout", "thru", "thu", "to", "togeth", "too", "took", "toward", "toward",
 "tri", "tri", "truli", "try", "try", "ts", "twice", "two", "u", "un", "under", "unfortun", "unless", "unlik",
 "until", "unto", "up", "upon", "us", "us", "us", "us", "us", "us", "usual", "v", "valu", "variou", "veri",
 "via", "viz", "vs", "w", "want", "want", "wa", "wa", "wasnt", "wai", "we", "we", "we'll", "we're", "we",
 "wed", "welcom", "well", "went", "were", "were", "werent", "weve", "what", "what'", "whatev", "what",
 "when", "whenc", "whenev", "where", "where'", "whereaft", "wherea", "wherebi", "wherein", "where",
 "whereupon", "wherev", "whether", "which", "while", "whither", "who", "who'", "whoever", "whole",
 "whom", "who", "whose", "why", "will", "will", "wish", "with", "within", "without", "wo", "wonder",
 "wont", "would", "would", "wouldnt", "wouldn?t", "x", "y", "ye", "yet", "you", "you", "you'll", "you're",
 "you", "youd", "youll", "your", "your", "your", "yourself", "yourselv", "youv", "z", "zero"
    );
  }

}

?>