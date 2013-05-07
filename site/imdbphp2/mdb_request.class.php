<?php

class MDB_Request {
	private $url;
	private $httpClient;
	private $content;

	function MDB_Request($url) {
		$this->setURL($url);

		$this->httpClient = new OpenDbSnoopy(TRUE);
	}

	function sendRequest() {
		$this->content = $this->httpClient->fetchURI($this->url);
		return $this->content !== FALSE;
	}

	function setURL($url) {
		$this->url = $url;
	}

	function getresponseheader($header = false) {
		$headers = $this->getLastResponseHeaders();
		foreach ($headers as $head) {
			if (is_integer(strpos($head, $header))) {
				$hstart = strpos($head, ": ");
				$head = trim(substr($head, $hstart + 2, 100));
				return $head;
			}
		}
	}

	function getLastResponseHeaders() {
		return $this->httpClient->headers;
	}

	function getResponseBody() {
		return $this->content;
	}
}
