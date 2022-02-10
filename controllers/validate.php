<?php
  class XmlValidator
  {
      /**
       * @var string
       */
      protected $feedSchema = __DIR__ . '/tei_all.rng';
      /**
       * @var int
       */
      public $feedErrors = 0;
      /**
       * Formatted libxml Error details
       *
       * @var array
       */
      public $errorDetails;
      /**
       * Validation Class constructor Instantiating DOMDocument
       *
       * @param \DOMDocument $handler [description]
       */
      public function __construct()
      {
          $this->handler = new \DOMDocument('1.0', 'utf-8');
      }
      /**
       * @param \libXMLError object $error
       *
       * @return string
       */
      private function libxmlDisplayError($error)
      {
          $errorString = "Error $error->code in $error->file (Line:{$error->line}):";
          $errorString .= trim($error->message);
          return $errorString;
      }
      /**
       * @return array
       */
      private function libxmlDisplayErrors()
      {
          setlocale(LC_ALL, 'fr_FR.UTF-8');
          $errors = libxml_get_errors();
          $result  []  = $errors;
          foreach ($errors as $error) {
              //$result[] = $this->libxmlDisplayError($error);
              //$result[] = $this->$error;
          }
          libxml_clear_errors();
          return $result;
      }

      /**
       * Validate Incoming Feeds against listed Schema
       *
       * @param resource $feeds
       *
       * @return bool
       *
       * @throws \Exception
       */
      public function validateFeeds($feeds)
      {
          if (!class_exists('DOMDocument')) {
            throw new \DOMException("'DOMDocument' class not found !");
            return false;
          }

          if (!file_exists($this->feedSchema)) {
            throw new \Exception('Schema is missing, please add schema to feedSchema property.');
            return false;
          }

          libxml_use_internal_errors(true);
          $header = file_get_contents(__DIR__ . '/header.xml');
          $footer = '</body></text></tei:TEI>';
          $this->handler->loadXML($header . $feeds . $footer, LIBXML_NOBLANKS);

          if (!$this->handler->relaxNGValidate($this->feedSchema)) {
      			$this->errorDetails = $this->libxmlDisplayErrors();
      			$this->feedErrors   = 1;
          } else {
             return true;
          };
      }
      /**
       * Display Error if Resource is not validated
       *
       * @return array
       */
      public function displayErrors()
      {
        return $this->errorDetails;
      }
  }
