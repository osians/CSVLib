# CSVLib Exemplo

Uma biblioteca CSV simples e orientada a objetos, criada para a leitura de arquivos CSV e para uso em artigo do site http://sooho.com.br .
Por momento essa biblioteca faz apenas a leitura de dados. Para uma versão futura, possivelmente irei implementar métodos novos, como o retorno de dados via ResultSet, Filtro de dados, Filtro por Colunas e a possibilidade de Escrita.

## Como usar

Fique a vontade para baixar, usa-la como quiser, alterar ou sugerir melhorias. Use o arquivo `exemplo_leitura.php` como referência. 

### Instanciando a Classe
O primeiro passo para usar, é intanciar a classe CSV. Toda a biblioca usa Namespace, para evitar conflitos com outros sistemas - visto que Csv e Arquivo, são nomes bem comuns.
Comece por inserir a biblioteca e instanciar a classe usando o seguinte código:

```php
# Incluindo a biblioteca CSV
require_once './lib/ARQUIVOS/csv.class.php' ; 

# Instanciando o Objeto de Manipulação de dados
$csv = new \ARQUIVOS\Csv( 'movimentos_financeiros.csv',',','"' );

```

A classe `Csv` recebe 3 parametros no total. Sendo:
 1. Caminho para o arquivo CSV
 2. Delimitador de Colunas do arquivo
 3. Identificador de Strings
caso não passe os 2 últimos parametros, a biblioteca irá considerar como padrão ";" como delimitador de colunas e '"' como idntificador de Strings.


### Leitura de dados

A leitura de dados é realizada com o método `ler()` da classe `Csv`.
Esse método, também pode receber os 3 parametros citado acima.

```php

# obtendo os dados e realizando um Loop
# com foreach
foreach( $csv->ler() as $linha )
    var_dump( $linha );

```

### Outras possibilidades

Existem algumas funções extras que podem ser de alguma utilidade. Seguem!

```php
/**
 * Caminho e Nome do Arquivo
 * saida: string; algo como 'teste_importe__.csv';
 */
echo $csv->filepath() ; 

/**
 * Número total de registros 
 * dentro do arquivo
 * saida: valor int;
 */
echo $csv->numRows() ;

/**
 * Obtendo o tamanho do arquivo
 * formatado em B,KB,MB,GB.
 * saida: Array('size','unit');
 */
$size = $csv->size();
echo $size['size'] . $size['unit'] ;

/**
 * Obtendo a data em que o arquivo foi 
 * Modificado.
 * Nota: Precisa configurar em seu sistema 
 * o timezone com date_default_timezone_set();
 * saida: string; algo como 10/04/2017 19:42:02
 */
echo $csv->dataModificacao() ;

/**
 * Existe a data em que o arquivo foi acessado 
 * por ultimo.
 * saida: string; algo como 10/04/2017 19:42:02
 */
echo $csv->dataAcesso() ;


/**
 * Você também pode transformar o resultado final 
 * em um objeto, se assim desejar e achar mais 
 * confortavel o uso de oop.
 * A conversão funciona pelo proprio PHP via typecast.
 */
foreach( $csv->ler() as $linha ){
    $linha = (object) $linha;
    var_dump( $linha );
}

```

### Concluindo

Espero que essa simples classe possa ser de alguma ajuda a quem a utilizar. Caso, necessite mais detalhes, sinta-se a vontade para acessar o site http://sooho.com.br pois contém um material mais detalhado sobre como a biblioteca foi elaborada.

Obrigado.
sem mais, 
Wanderlei Santana <sans.pds@gmail.com>