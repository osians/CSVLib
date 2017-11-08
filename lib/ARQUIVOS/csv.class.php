<?php

namespace ARQUIVOS ;

require_once 'arquivo.class.php';

class CsvException extends \Exception
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
/**
 * 
 * Classe responsavel por interpretar o conteudo 
 * de arquivos CSV. Utiliza a Classe "Arquivo"
 * para ter acesso as informações de um arquivo
 * fisico e assim poder maneja-lo.
 *
 * @package ARQUIVOS
 * @version 201704102135
 * @copyright Copyleft - 2017
 * @author Wanderlei Santana <sans.pds@gmail.com>
 * 
*/
class Csv extends \ARQUIVOS\Arquivo
{
	private $delimitador = ";" ;
	private $enclosure = '"' ;

	/**
	 * @var array - guarda o nome dos indices que estao contidos
	 * na primeira linha do arquivo CSV
	 */
    private $indices = array();

    private $num_rows = 0;

    /**
     * Metodo construtor
     * --------------------------
     * @param string $arquivo - arquivo contendo CSV
     * @param char $delimitador - caractere delimitador de colunas
     * @param char $enclosure - caracter de scape para strings
     */
	function __construct( $arquivo = null , $delimitador = null, $enclosure = null )
	{
		parent::__construct( $arquivo );
		$this->setDelimitador( $delimitador );
		$this->setEnclosure( $enclosure );
	}

	public function setArquivo( $arquivo = null )
	{
		parent::setCaminho( $arquivo );
	}

	public function setDelimitador( $value = null )
	{
		if($value == null) return;
		$this->delimitador = $value;
	}

	public function setEnclosure( $value = null )
	{
		if($value == null) return;
		$this->enclosure = $value;
	}

	/**
	 * Funcao responsavel por processar todas
	 * as linhas do arquivo CSV e retornar
	 * os dados em forma de Array
	 * ---------------------------
	 *
     * @param string $arquivo - arquivo contendo CSV
     * @param char $delimitador - caractere delimitador de colunas
     * @param char $enclosure - caracter de scape para strings
	 * @return array - linhas do arquivo
	 */
	public function ler( $arquivo = null , $delimitador = null, $enclosure = null  )
	{
		if( $arquivo != null )
			parent::__construct( $arquivo );

		if( $delimitador != null )
			$this->setDelimitador( $delimitador );
		
		if( $enclosure != null )
			$this->setEnclosure( $enclosure );

		$_dados = array();

		# abrindo o arquivo com a classe Pai
		parent::abrir();

	    # @var boolean - verifica se passou primeira linha
	    $__primeira = true ;

	    while (($_data = fgetcsv( parent::getHandle(), 1024, "\\" ) ) !== FALSE):
	        $this->num_rows++;

			$_array_linha = str_getcsv( $_data[0], $this->delimitador, $this->enclosure );

	    	if($__primeira):

	    		# Padronizando indices para evitar erros
	    		# --------------------------------------
	    		# trasnforma tudo em minusculo
	    		# removendo a acentuacao das palavras para criar indices corretos
	    		# removendo espacos
	    		# trocando hiffem por underline
	    		foreach( $_array_linha as $key => $value )
	    			$_array_linha[$key] = strtolower( $this->tratarIndices( $value ) );

				# preenche a variavel indices com os nomes localizados na primeira
				# linha do arquivo CSV
	    		$this->indices = $_array_linha ;

	    		# FALSE para evitar que a primeira linha seja lida novamente
	    		$__primeira = false;

	    		# continue para nao percorrer o resto do codigo dentro
	    		# deste laco
	    		continue;
			endif;

	        # aqui, criamos um novo array, associando cada um dos
	        # indices aos dados encontrados na linha.
	        # Dessa forma, as informações do CSV podem ser
	        # acessados com $csv['nome'], $csv['sobrenome'] ao
	        # invés de usar $csv[0], $csv[6]
	        try
	        {
				$_dados[] = array_combine( $this->indices, $_array_linha );
			}
			catch( CsvException $e ){
				throw new CsvException( 
					"Existe um erro de lógica na formação do conteudo CSV", $e->getCode() );
			}

	    endwhile;

	    return $_dados;
	}

	/**
	 * Remove BOM PHP 
	 * 
	 * Correção fornecida por: Diego Souza - DeeSouza ( https://github.com/DeeSouza )
	 * issue: https://github.com/osians/CSVLib/issues/1
	 * 
	 * @param $text string - A ser corrigida removendo Codificacao BOM
	 * @return  string
	 */
	public function removeBOM($text){
	   $bom 	= pack('H*','EFBBBF');
	   $text 	= preg_replace("/^$bom/", '', $text);
	   return $text;
	}

	/**
	 * Remove acentuacao de uma string
	 * -------------------------------
	 *
	 * @param $str String - String para ser removido os acentos
	 * @return string
	 */
	private function tratarIndices($str)
	{
		$str = $this->removeBOM($str);
	    $str = utf8_encode( $str );

	    $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
	    $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
	    $str = str_replace($a, $b, $str);

	    $str = str_replace(array(' ','-','/'), '_', $str );
	    $str = str_replace(array('Â','º','ª'),'', $str);

	    return $str;
	}

	/**
	 * Retorna o total de Linhas
	 * --------------------------
	 * Note que a primeira linha com
	 * o cabecalho tambem faz parte da conta
	 *
	 * @return int
	 */
	public function numRows(){
		return $this->num_rows;
	}
}
