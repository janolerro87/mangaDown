<?php

class mangaDown {
	/*
	 * Cadena para almacenar el numero del capitulo
	 */
	public $chapter = null;

	/*
	 * Cadena para almacenar el nombre del capitulo
	 */
	public $name = null;

	/*
	 * Cadena para almacenar el traductor del capitulo
	 */
	public $scanlation = null;

	/*
	 * Cadena para almacenar el lector de donde se extraera el capitulo
	 */
	public $reader = null;

	/*
	 * Cadena para indicar la ruta absoluta en donde se descargara el capitulo
	 */
	private $home = '/home/knor/public_html/mangaDown/';


	/**
	 * Metodo que se encarga de descargar en una carpeta (de forma binaria) todas las posibles imagenes del capitulo
	 * @param  array $urls  Arreglo con cada una de las direcciones url de cada imagen del capitulo
	 * @return integer      Valor que indica el estado de la descarga de las imagenes
	 *                      0: No existen direcciones url
	 *                      1: Las imagenes se descargaron completamente
	 *                      2: La carpeta ya fue creada
	 */
	public function downloadImages($urls) {

		if ($urls) {
			
			$folder = $this->home.'/Mangas/[NU] '.ucwords($this->name) . ' '.$this->chapter;
			if( file_exists($folder) ) {
				return 2;
			} else {
				mkdir($folder, 0777);
				foreach ($urls as $url) {
					$ch = curl_init($url);
					$fp = fopen("$folder/".basename($url), 'wb');
					curl_setopt($ch, CURLOPT_FILE, $fp);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_exec($ch);
					curl_close($ch);
					fclose($fp);
					sleep(1);
					unset($ch);
				}

				return 1;
			}
		}

		return 0;
	}
	/**
	 * Metodo para obtener las direcciones (URLs) web de cada imagen
	 * @param  string $chapter  Numero del capitulo a descargar
	 * @return array            Arreglo con las direcciones (URLs) web con todas las imagenes que conforma el capitulo
	 */
	public function getImgUrls($chapter) {
		$img = explode('"', $this->getUrl($chapter)[93]); // <img>
		$urlImg0 = $img[3]; // url de la imagen
		$text = strip_tags($this->getUrl($chapter)[77]);
		$text = explode('P', $text);
		unset($text[0]);
		$nImgs = count($text);

		$diag = strrpos($urlImg0, '/');
		$base = substr($urlImg0, 0, ($diag + 1));

		for ($i=0; $i < $nImgs; $i++) { 
			$urls[$i] = $base.$i.'.jpg';
		}
		return $urls;
	}
	/**
	 * Metodo para extraer el codigo fuente de la pagina de la primer imagen del capitulo
	 * @param  string $chapter Numero del capitulo
	 * @return string          Cadena del codigo fuente de la pagina
	 */
	public function getUrl($chapter) {
		
		if($this->reader == 'nu'){
			$urlm = 'http://narutouchiha.com/manga/reader/read/'.$this->name.'/es/0/'.$chapter.'/page/1';
			$get = file($urlm);
			return $get;
		} elseif($this->reader == 'sm'){
			$main = 'http://submanga.com/c/';
			$urlm = $main.$chapter;

			$get = file("$urlm/1");

			$event = " onchange=\"window.location.href='$urlm/'+this.value\"";
			return str_replace($event, '', $get[0]);
		}
	}
	/**
	 * Metodo para leer las imagenes del capitulo previamente descargado
	 * @param  string $folder   Nombre de la carpeta del capitulo donde se localizan las imagenes
	 * @return array            Arreglo con las rutas locales de cada imagen
	 */
	public function listFiles($folder) {
		$root = $this->home . $folder.'/';
		$files = glob("$root*");
		if($files){
			return $files;
		} else {
			return false;
		}
	}

	public function listImages($root) {
		if($root) {
			$root = $root.'/';
			if ($dh = opendir($root)) {
				while (($file = readdir($dh)) !== false) {
					if($file != "." && $file != ".." ) {
						$files[] = $file;
					}
				}
				closedir($dh);
			}
			sort($files, SORT_NATURAL);
			return $files;
		}

		return false;
	}
	/**
	 * Metodo para obtener el nombre y el numero del capitulo
	 * @param  string $url    Direccion url base del capitulo
	 * @param  string $reader Lector de donde se extraera el capitulo
	 * @return string         Numero del capitulo
	 */
	public function parseChapter($url, $reader) {
		$ch = $url;
		$this->reader = $reader;
		$ch = str_replace("http://", "", $ch);
		$ch = explode('/', $ch);
		if($reader == 'nu') {
			$this->chapter = $ch[7];

			$this->name = $ch[4];
		}
		else {
			if(count($ch) == 4) {
				$this->chapter = $ch[3];
				$this->name = strtolower($ch[1]);
			}
			else {
				$this->chapter = $ch[0];
			}
		}
		return $this->chapter;
	}

}