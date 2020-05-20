<?php

/**
 * 
 * Classe resposnsavel por manipular arquivos 
 * fisicos. Verifica se um arquivo e' acesivel, 
 * se pode ser lido e escrito, além de prover 
 * algumas poucas informacoes basicas sobre o 
 * mesmo.
 *
 * @package CSVLib
 * @version 202005200110
 * @copyright Copyleft - 2020
 * 
 * @author Wanderlei Santana <sans.pds@gmail.com>
 * 
*/
class FileManager
{
    /**
     * File Handler
     *
     * @var Resource $_filePointer
     */
    private $_filePointer = null;

    /**
     * Abertura por padrao sera Leitura "r"
     * 
     * MODOS DE LEITURA
     * 
     * [r]  Open a file for read only. File pointer starts at the beginning of the file
     * [w]  Open a file for write only. Erases the contents of the file or creates a new file if it doesn't exist. File pointer starts at the beginning of the file
     * [a]  Open a file for write only. The existing data in file is preserved. File pointer starts at the end of the file. Creates a new file if the file doesn't exist
     * [x]  Creates a new file for write only. Returns FALSE and an error if file already exists
     * [r+] Open a file for read/write. File pointer starts at the beginning of the file
     * [w+] Open a file for read/write. Erases the contents of the file or creates a new file if it doesn't exist. File pointer starts at the beginning of the file
     * [a+] Open a file for read/write. The existing data in file is preserved. File pointer starts at the end of the file. Creates a new file if the file doesn't exist
     * [x+] Creates a new file for read/write. Returns FALSE and an error if file already exists
     * 
     * @var $_modo - Modo de abertura do arquivo.
    **/
    private $_modo = "r";

    /**
     * Caminho onde se localiza o arquivo
     * 
     * @var String $_filePath
     */
    private $_filePath = null;

    /**
     * Constructor
     *
     * @param string $_filePath - caminho para o arquivo
     * @param string $_modo - modo de abertura do arquivo
     * 
     * @return void
     */
    public function __construct($_filePath = null, $_modo = null)
    {
        $this->setFilePath($_filePath)->setModo($_modo);
    }

    /**
     * Retorna Ponteiro para o Arquivo
     *
     * @return Resource
     */
    public function getFilePointer()
    {
        return $this->_filePointer;
    }
    
    /**
     * Seta o caminho onde o arquivo se encontra
     *
     * @param String $filePath
     *
     * @return FileManager
     */
    public function setFilePath($filePath = null)
    {
        if ($filePath == null) {
            return $this;
        }

        $this->_filePath = $filePath;

        return $this;
    }

    /**
     * Retorna Caminho para o Arquivo
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->_filePath;
    }
    
    /**
     * Seta o modo de abertura do Arquivo
     *
     * @param string $modo
     * 
     * @return FileManager
     * 
     * @throws Exception - Modo de abertura de arquivos informado é inválido
     */
    public function setModo($modo = null)
    {
        if($modo == null) {
            return $this;
        }

        if (!$this->_isValidMode($modo)) {
            throw new Exception(
                "Modo de abertura de arquivos informado é inválido: " . $modo 
            );
        }
        
        $this->_modo = $modo;

        return $this;
    }

    /**
     * Verifica se o modo de abertura do arquivo e' valida
     * 
     * @param type $mode
     * 
     * @return type bool
     */
    protected function _isValidMode($mode)
    {
        return (in_array($mode, array('r','w','a','x','r+','w+','a+','x+')));
    }
    
    /**
     * Abre arquivo para leitura ou escrita
     *
     * @param String $filepath
     * @param String $modo
     *
     * @return Resource
     *
     * @throws Exception
     */
    public function open($filepath = null, $modo = null)
    {
        $this->setFilePath($filepath)->setModo($modo)->checkFile();
        
        if ($this->getFilePath() == null) {
            throw new Exception("Você não informou um arquivo a ser aberto.");
        }

        $this->_filePointer = fopen($this->getFilePath(), $this->_modo);

        return $this->_filePointer;
    }

    /**
     * Valida arquivo para saber se pode ser aberto
     *
     * @return FileManager
     * 
     * @throws Exception
     */
    protected function checkFile()
    {
        if (is_null($this->getFilePath())) {
            return $this;
        }

        if (!file_exists( $this->getFilePath())) {
            throw new Exception(
                'Não pode encontrar o arquivo : '.$this->getFilePath());
        }

        if (!is_file( $this->getFilePath() )) {
            throw new Exception(
                "Parece que o caminho informado ({$this->getFilePath()}) não é de um arquivo");
        }

        if (!is_readable( $this->getFilePath() )) {
            throw new Exception(
                "O Arquivo ({$this->getFilePath()}) não pode ser lido");
        }

        if (!is_writable($this->getFilePath())) {
            throw new Exception(
                "O Arquivo ({$this->getFilePath()}) não pode ser escrito" );
        }
        
        return $this;
    }

    /**
     * Retornar o Tamanho do arquivo formatado en GB, MB, KB ou B
     *
     * Dado um tamanho de arquivo, atraves do
     * filesize( 'caminho_do_arquivo' ), essa
     * funcao retorna a informacao formatada
     * em GB,MB,KB ou B
     *
     * @return Array - array('original', 'size', 'unit')
     */
    public function size()
    {
        $retorno = array('original' => 0, 'size' => 0, 'unit' => 'B');
        
        if ($this->getFilePath() == null) {
            return $retorno;
        }

        $tamanho = filesize($this->getFilePath());

        if ($tamanho >= 1073741824) {
            $retorno['size'] = number_format(($tamanho / 1073741824), 2);
            $retorno['unit'] = 'GB';
            return $retorno;
        }
        
        if ($tamanho >= 1048576) {
            $retorno['size'] = number_format(($tamanho / 1048576), 2);
            $retorno['unit'] = 'MB';
            return $retorno;
        }
        
        if ($tamanho >= 1024) {
            $retorno['size'] = number_format(($tamanho / 1024), 2);
            $retorno['unit']= 'KB';
        }
        
        return $retorno;
    }

    /**
     * Retorna Data de Modificação do Arquivo
     *
     * @return String
     */
    public function dataModificacao()
    {
        return date("d/m/Y H:i:s", filemtime( $this->getFilePath()));
    }

    /**
     * Retorna a data de ultimo acesso do arquivo
     *
     * Note que, para a data ser retornada de forma correta,
     * e' necessario que o sistema que inclui esse arquivo
     * tenha setado o timezone atraves de date_default_timezone_set();
     *
     * @return string
     */
    public function dataAcesso()
    {
        return date("d/m/Y H:i:s", fileatime($this->getFilePath()));
    }

    function __destruct()
    {
       	if (!is_null($this->getFilePointer())) {
            fclose($this->getFilePointer());
        }
    }
}
