**PHP/LARAVEL**

**TAREFA 1**
temos que criar uma API para consulta publica exempli citado brasilapi: Get https://brasilapi.com.br/api/cnpj/V1/{CNPJ} Sem mascara com endpoints
caso tenha mesmo CNPJ seja enviado novamente deve ser atualizado novamente cadastro de pessoa jurídica 


**TAREFA 2 LISTAGEM GERAL**

listagem geral
implementar paginação dos resultados
permitir filtros por uf, município ou situação cadacastral
retornar os dados em formato json
permitir controlar quantidade de registro por pagina 
criar endpoints para consuta de dados completos de uma empresa
id interno do registro ou 
numero do CNPJ



**TAREFA 3 TRATATIVAS DE ERRO** 

definir timout  na comunicação 
tratar erros de cinexão e indisponibilidade do serviço externo
tratar respostar invalidade ou inesperadas
retorno códigos http adequados para cada situação
evitar que exceç\~ies não tratadas sejam propagadas ao consumidor da api
tem que ser tudo consistente 



**TAREFA 4 DOCKER**

DOCKER
criar dockerfile para apliação
criar arquivo docker-composer.yml
configurar container para execução da aplicação laravel
configurar container para banco de dados relacional 
configurar comunicação entre os containers
configurar variáveis de ambiente 
garantir persistência dos bancos de dados 
ter instruções no README.MD



**TAREFA 5 TESTE AUTOMATIZADO** 

testes de sincronizações bem sucedidas simulando responsta apo externa  validando dados persistentes
teste validação para CNPJ invalido
teste comportamento quando o serviço externo retorna erro oi quando registro não é encontrado
teste de idempotencia, garantindo que consultas repetidas não gerem registro duplicados



**TAREFA 6 VERSIONAMENTO GIT** 

criar um repositória para o projeto
realizar commits durante o desenvolvimento 
utilizar mensagens durante o desenvolvimento 
organizar o desenvolvimento utilizando branches
disponibilizar o histórico de versionamento.


**AUTONOMIA E DECISÕES TECNICAS**

podendo usar podendo usar rotas, verbos http, parâmetros de entratada e estrutura resposta emjson
arquitetura interna das camadas laravel, podendo usar services actions repositóries, dtos, form request e api resouces
bancos de dados definição do schema, tipos de colunas, indeces e estrutura de armazenamento
integração externa estratégia de comunicação, timeout tratamentos de erro e organização do services responsável da consulta
processamento solução poderá ser implementada de forma síncrona
uso de files, Jobs ou redis será considerado diferencial não sendo obrigatório



**DOCUMENTAÇÃO - README.MD**

Descrição e objetivo da aplicação
pré-requisito para execução 
instrução para clonar o repositório
orientações para configuração do arquivo .env
instruções para executar as migrations
exemplos de utilização dos endpoints, utilizando cURL
ou documentação equivalente 
comando para execução dos teste automatizados
breve explicação das decisões técnicas e arquitetura adotadas



**PROCEDIMENTO DE ENTREGA**

Garantir que todas as alterações estejam commitadas e enviadas ao repositório git
disponibilizar código branch principal (main ou master (master))
garantir que o arquivo .env example contenha todas as variáveis 
realizar um teste de inicialização do projeto utilizando Docker compose 
validar os principais  endpionts desenvolvidos


enviar e-mail **Vagner.duarte@mt.sebrae.com.br**









