<?php

require_once 'FileManager.php';

/**
 * 
 * Classe responsavel por interpretar o conteudo 
 * de arquivos CSV. Utiliza a Classe "Arquivo"
 * para ter acesso as informações de um arquivo
 * fisico e assim poder maneja-lo.
 *
 * @package CSVLib
 * @version 202005200200
 * @copyright Copyleft - 2020
 *
 * @author Wanderlei Santana <sans.pds@gmail.com>
 * 
*/
class CsvReader extends FileManager
{
    /**
     * Caracter delimitador de Colunas
     *
     * @var String
     */
    private $_delimitador = ";";

    /**
     * Caracter indicador de texto
     *
     * @var String
     */
    private $_enclosure = '"';

    /**
     * Guarda o Nome das Colunas
     * 
     * @var Array
     */
    private $_indices = array();

    /**
     * Total de Registros encontrados no arquivo
     *
     * @var Integer
     */
    private $_numRows = 0;

    /**
     * Constructor
     *
     * @param string $arquivo - arquivo contendo CSV
     * @param char $delimitador - caractere delimitador de colunas
     * @param char $enclosure - caracter de scape para strings
     * 
     * @return void
     */
    function __construct($arquivo = null, $delimitador = null, $enclosure = null)
    {
        parent::__construct();
        $this->_inicializarArquivo($arquivo, $delimitador, $enclosure);
    }

    /**
     * Seta o caractere que delimita os campos do arquivo CSV.
     * Os mais comuns são ";" e ",".
     * 
     * @param String $delimiter
     *
     * @return CsvReader
     */
    public function setDelimitador($delimiter = null)
    {
        if ($delimiter == null) {
            return;
        }

        $this->_delimitador = $delimiter;
        return $this;
    }

    /**
     * Retorna o caracter delimitador do arquivo CSV
     *
     * @return String
     */
    public function getDelimitador()
    {
        return $this->_delimitador;
    }
    
    /**
     * Seta o caractere que delimita um campo do tipo texto
     *
     * @param String $value - normalmente " ou '
     *
     * @return CsvReader
     */
    public function setEnclosure($value = null)
    {
        if ($value == null) {
            return $this;
        }
        
        $this->_enclosure = $value;
        return $this;
    }

    /**
     * retornar o caracter de Enclosure de Texto
     *
     * @return String
     */
    public function getEnclosure()
    {
        return $this->_enclosure;
    }
    
    /**
     * Seta os Indices ou Colunas da tabela CSV
     *
     * @param Array $indices
     *
     * @return CsvReader
     */
    public function setIndices($indices = array())
    {
        $this->_indices = $indices;
        return $this;
    }
    
    /**
     * Retorna Array com os nomes das Colunas do arquivo CSV
     *
     * @return Array
     */
    public function getIndices()
    {
        return $this->_indices;
    }
    
    /**
     * Funcao responsavel por processar todas
     * as linhas do arquivo CSV e retornar
     * os dados em forma de Array
     *
     *
     * @param string $arquivo - arquivo contendo CSV
     * @param char $delimitador - caractere delimitador de colunas
     * @param char $enclosure - caracter de scape para strings
     *
     * @return array - linhas do arquivo
     * 
     * @throws Exception
     */
    public function read($arquivo = null , $delimitador = null, $enclosure = null)
    {
        $this->_inicializarArquivo($arquivo, $delimitador, $enclosure);
        parent::open();

        $retorno = array();
        $firstLine = true;

        while (($row = fgetcsv(parent::getFilePointer(), 1024, "\\")) !== false) {
            $this->_numRows++;

            $linha = str_getcsv($row[0], $this->getDelimitador(), $this->getEnclosure());

            if ($firstLine) {
                $this->_tratarPrimeiraLinhaDoArquivo($linha);
                $firstLine = false;
                continue;
            }

            # aqui, criamos um novo array, associando cada um dos
            # indices aos dados encontrados na linha.
            # Dessa forma, as informações do CSV podem ser
            # acessados com $csv['nome'], $csv['sobrenome'] ao
            # invés de usar $csv[0], $csv[6]
            try {
                $retorno[] = array_combine($this->getIndices(), $linha);
            } catch (Exception $e) {
                throw new Exception( 
                    "Erro de lógica na formação do conteudo CSV", $e->getCode());
            }
        }

        return $retorno;
    }

    /**
     * Metodo usado para inicializar o arquivo CSV atraves da classe parent
     *
     * @param String $arquivo
     * @param string $delimitador
     * @param String $enclosure
     *
     * @return CsvReader
     */
    private function _inicializarArquivo($arquivo = null , $delimitador = null, $enclosure = null)
    {
        if ($arquivo != null) {
            parent::setFilePath($arquivo);
        }

        if ($delimitador != null) {
            $this->setDelimitador($delimitador);
        }
		
        if ($enclosure != null) {
            $this->setEnclosure($enclosure);
        }
        
        return $this;
    }
    
    /**
     * trata a primeira linha do arquivo CSV, tornando esta as colunas
     * ou indices do arquivo.
     *
     * @param Array $linha
     *
     * @return CsvReader
     */
    protected function _tratarPrimeiraLinhaDoArquivo($linha)
    {
        # Padronizando indices para evitar erros
        # 
        # trasnforma tudo em minusculo
        # removendo a acentuacao das palavras para criar indices corretos
        # removendo espacos
        # trocando hiffem por underline
        foreach ($linha as $key => $value) {
            $linha[$key] = strtolower($this->tratarIndices($value));
        }

        # preenche a variavel indices com os nomes localizados na primeira
        # linha do arquivo CSV
        $this->setIndices($linha);

        return $this;
    }


    /**
     * Remove BOM PHP 
     * 
     * Correção fornecida por: Diego Souza - DeeSouza ( https://github.com/DeeSouza )
     * issue: https://github.com/osians/CSVLib/issues/1
     * 
     * @param $text string - A ser corrigida removendo Codificacao BOM
     *
     * @return  string
     */
    public function removeBom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }

    /**
     * Remove acentuacao de uma string
     *
     * @param $text String - String para ser removido os acentos
     *
     * @return string
     */
    private function tratarIndices($text)
    {
        $str = utf8_encode($this->removeBom($text));

        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
        
        $str = str_replace($a, $b, $str);
        $str = str_replace(array(' ','-','/'), '_', $str );
        $str = str_replace(array('Â','º','ª'),'', $str);

        return $str;
    }

    /**
     * Retorna o total de Linhas
     *
     * Note que a primeira linha com
     * o cabecalho tambem faz parte da conta
     *
     * @return int
     */
    public function numRows()
    {
        return $this->_numRows;
    }
}
