<?php

namespace ARQUIVOS ;

class ArquivoException extends \Exception
{
	public function __construct($message = null, $code = 0)
	{
		parent::__construct($message, $code);
		error_log(
			'Erro em ' . $this->getFile() .
			' Linha: ' . $this->getLine() .
			' Erro:  ' . $this->getMessage()
		);
	}
}

class ArquivoNaoExisteException extends ArquivoException {}
class ArquivoNaoSuportadoException extends ArquivoException {}
class LogicaException extends ArquivoException {}

/**
 * 
 * Classe resposnsavel por manipular arquivos 
 * fisicos. Verifica se um arquivo e' acesivel, 
 * se pode ser lido e escrito, além de prover 
 * algumas poucas informacoes basicas sobre o 
 * mesmo.
 *
 * @package ARQUIVOS
 * @version 201704102135
 * @copyright Copyleft - 2017
 * @author Wanderlei Santana <sans.pds@gmail.com>
 * 
*/
class Arquivo
{
	/**
	 * File Handler Ponteiro de
	 * manipulacao do arquivo
	 * ------------------------
	 * @var $fp - null
	 */
	private $fp = null ;

	/**
	 * @var $modo - Modo de abertura do arquivo.
	 * por padrao sera Leitura "r"
	 * MODOS DE LEITURA
	 * -------------------------------------------
	 * r  Open a file for read only. File pointer starts at the beginning of the file
	 * w  Open a file for write only. Erases the contents of the file or creates a new file if it doesn't exist. File pointer starts at the beginning of the file
	 * a  Open a file for write only. The existing data in file is preserved. File pointer starts at the end of the file. Creates a new file if the file doesn't exist
	 * x  Creates a new file for write only. Returns FALSE and an error if file already exists
	 * r+ Open a file for read/write. File pointer starts at the beginning of the file
	 * w+ Open a file for read/write. Erases the contents of the file or creates a new file if it doesn't exist. File pointer starts at the beginning of the file
	 * a+ Open a file for read/write. The existing data in file is preserved. File pointer starts at the end of the file. Creates a new file if the file doesn't exist
	 * x+ Creates a new file for read/write. Returns FALSE and an error if file already exists
	**/
	private $modo = "r" ;

	/**
	 * Caminho onde se localiza o arquivo
	 * ----------------------------------
	 * @var $caminho - null
	 */
	private $caminho = null ;

	/**
	 * Metodo construtor
	 * @param string $caminho - caminho para o arquivo
	 * @param string $modo - modo de abertura do arquivo
	 */
	function __construct( $caminho = null, $modo = null)
	{
		$this->setCaminho( $caminho );
		$this->setModo( $modo );
	}

	public function abrir( $caminho = null, $modo = null )
	{
		$this->setCaminho( $caminho );
		$this->setModo( $modo );

		if( $this->caminho == null )
			throw new LogicaException( 
				"Você não informou um arquivo a ser aberto." );

		$this->fp = fopen( $this->caminho, $this->modo );
		return $this->fp ;
	}

	public function setCaminho( $caminho = null )
	{
		if( $caminho == null ) return;

		$this->caminho = $caminho ;

		# e verificando se nao havera erros
		# para abri-lo
		$this->checkFile();
		return true;
	}

	public function setModo( $modo = null )
	{
		if( $modo == null ) return;

		if( ! in_array($modo, array('r','w','a','x','r+','w+','a+','x+') ) )
			throw new ArquivoException(
				"Modo de abertura de arquivos informado é inválido: " . $modo );
		$this->modo = $modo;
	}

	protected function checkFile()
	{
		if(is_null($this->caminho)) return;

		if (!file_exists( $this->caminho ))
			throw new ArquivoNaoExisteException(
				'Não pode encontrar o arquivo : '.$this->caminho );

		if (!is_file( $this->caminho ))
			throw new ArquivoException(
				"Parece que o caminho informado (" . $this->caminho .
				") não é de um arquivo" );

		if (!is_readable( $this->caminho ))
			throw new ArquivoNaoSuportadoException(
				"O Arquivo (".$this->caminho.") não pode ser lido" );

		if (!is_writable( $this->caminho ))
			throw new ArquivoNaoSuportadoException(
				"O Arquivo (".$this->caminho.") não pode ser escrito" );
	}

	/**
	 * Retornar o Tamanho do arquivo formatado
	 * ---------------------------------------
	 * Dado um tamanho de arquivo, atraves do
	 * filesize( 'caminho_do_arquivo' ), essa
	 * funcao retorna a informacao formatada
	 * em GB,MB,KB ou B
	 *
	 * @return array - array( 'size', 'unit' )
	 * retorna um boolean false caso o arquivo
	 * nao tenha sido setado.
	 */
	public function size()
	{
		if( $this->caminho == null ) return false;

		# pega informacao de tamanho
		$tamanho = filesize( $this->caminho ) ;

	    if ($tamanho >= 1073741824){
	        $tamanho = number_format(($tamanho / 1073741824), 2);
	        $unit = 'GB';
	    }
	    else if ($tamanho >= 1048576){
	        $tamanho = number_format(($tamanho / 1048576), 2);
	        $unit = 'MB';
	    }
	    else if ($tamanho >= 1024){
	        $tamanho = number_format(($tamanho / 1024), 2);
	        $unit = 'KB';
	    }
	    else if ($tamanho >= 0){
	        $unit = 'B';
	    }
	    else{
	        $tamanho = '0';
	        $unit = 'B';
	    }
	    return array( 'original' => filesize( $this->caminho ), 'size' => $tamanho, 'unit' => $unit );
	}

	public function dataModificacao(){
		return date( "d/m/Y H:i:s", filemtime( $this->caminho ) );
	}

	/**
	 * Retorna a data de ultimo acesso do arquivo
	 * ------------------------------------------
	 * Note que, para a data ser retornada de forma correta,
	 * e' necessario que o sistema que inclui esse arquivo
	 * tenha setado o timezone atraves de date_default_timezone_set();
	 *
	 * @return string
	 */
	public function dataAcesso(){
		return date( "d/m/Y H:i:s", fileatime( $this->caminho ));
	}

	public function filepath(){
		return $this->caminho;
	}

	public function getHandle(){
		return $this->fp;
	}

    function __destruct() {
       	if(!is_null($this->fp))
       		fclose( $this->fp ) ;
    }
}