<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Simulado;
use App\Models\SimuladoCategory;
use App\Models\SimuladoQuestion;
use Illuminate\Support\Facades\DB;

class SimuladoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando categorias de simulados...');
        
        // Criar categorias
        $categories = [
            ['name' => 'Programação'],
            ['name' => 'Estruturas de Dados'],
            ['name' => 'Algoritmos'],
            ['name' => 'Desenvolvimento Web'],
        ];
        
        foreach ($categories as $category) {
            SimuladoCategory::firstOrCreate($category);
        }
        
        $this->command->info('Criando simulados...');
        
        // Simulado 1: Fundamentos de Programação
        $this->createSimuladoFundamentos();
        
        // Simulado 2: Estruturas de Dados
        $this->createSimuladoEstruturasData();
        
        // Simulado 3: Algoritmos e Complexidade
        $this->createSimuladoAlgoritmos();
        
        // Simulado 4: Desenvolvimento Web
        $this->createSimuladoWeb();
        
        $this->command->info('Simulados criados com sucesso!');
    }
    
    private function createSimuladoFundamentos()
    {
        $category = SimuladoCategory::where('name', 'Programação')->first();
        
        $simulado = Simulado::create([
            'title' => 'Fundamentos de Programação',
            'description' => 'Teste seus conhecimentos sobre conceitos básicos de programação, incluindo variáveis, estruturas de controle, funções e paradigmas de programação.',
            'category_id' => $category->id,
            'duration' => 3600, // 60 minutos
            'min_score' => 70,
            'max_attempts' => 3,
            'type' => 'exam',
            'allow_navigation' => true,
            'allow_save_progress' => true,
            'show_feedback' => true,
            'status' => 'active',
        ]);
        
        $questions = [
            [
                'statement' => 'Qual é a principal diferença entre uma variável e uma constante?',
                'options' => [
                    ['id' => 'A', 'text' => 'Variáveis podem mudar de valor durante a execução, constantes não'],
                    ['id' => 'B', 'text' => 'Constantes são mais rápidas que variáveis'],
                    ['id' => 'C', 'text' => 'Variáveis ocupam mais memória que constantes'],
                    ['id' => 'D', 'text' => 'Não há diferença entre elas']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Variáveis podem ter seus valores alterados durante a execução do programa, enquanto constantes mantêm o mesmo valor.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'easy'
            ],
            [
                'statement' => 'O que é um algoritmo?',
                'options' => [
                    ['id' => 'A', 'text' => 'Uma linguagem de programação'],
                    ['id' => 'B', 'text' => 'Um conjunto de instruções para resolver um problema'],
                    ['id' => 'C', 'text' => 'Um tipo de dado'],
                    ['id' => 'D', 'text' => 'Um erro no código']
                ],
                'correct_answer' => 'B',
                'explanation' => 'Um algoritmo é uma sequência finita de instruções bem definidas para resolver um problema específico.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'easy'
            ],
            [
                'statement' => 'Qual estrutura de controle é usada para repetir um bloco de código?',
                'options' => [
                    ['id' => 'A', 'text' => 'if-else'],
                    ['id' => 'B', 'text' => 'switch-case'],
                    ['id' => 'C', 'text' => 'for/while'],
                    ['id' => 'D', 'text' => 'try-catch']
                ],
                'correct_answer' => 'C',
                'explanation' => 'Estruturas de repetição como for, while e do-while são usadas para executar um bloco de código múltiplas vezes.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'easy'
            ],
            [
                'statement' => 'O que é programação orientada a objetos?',
                'options' => [
                    ['id' => 'A', 'text' => 'Um paradigma baseado em objetos que encapsulam dados e comportamentos'],
                    ['id' => 'B', 'text' => 'Uma linguagem de programação específica'],
                    ['id' => 'C', 'text' => 'Um tipo de banco de dados'],
                    ['id' => 'D', 'text' => 'Uma ferramenta de desenvolvimento']
                ],
                'correct_answer' => 'A',
                'explanation' => 'POO é um paradigma que organiza o código em objetos que combinam dados (atributos) e comportamentos (métodos).',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ],
            [
                'statement' => 'Qual é a função principal de um compilador?',
                'options' => [
                    ['id' => 'A', 'text' => 'Executar o programa'],
                    ['id' => 'B', 'text' => 'Traduzir código fonte para código de máquina'],
                    ['id' => 'C', 'text' => 'Debugar o código'],
                    ['id' => 'D', 'text' => 'Criar interfaces gráficas']
                ],
                'correct_answer' => 'B',
                'explanation' => 'Um compilador traduz o código fonte escrito em uma linguagem de alto nível para código de máquina ou bytecode.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ]
        ];
        
        // Adicionar mais 15 questões para completar 20
        $moreQuestions = [
            [
                'statement' => 'O que é recursão em programação?',
                'options' => [
                    ['id' => 'A', 'text' => 'Uma função que chama a si mesma'],
                    ['id' => 'B', 'text' => 'Um tipo de loop'],
                    ['id' => 'C', 'text' => 'Uma estrutura de dados'],
                    ['id' => 'D', 'text' => 'Um erro de programação']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Recursão é uma técnica onde uma função chama a si mesma para resolver subproblemas menores.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ],
            [
                'statement' => 'Qual é a diferença entre parâmetro e argumento?',
                'options' => [
                    ['id' => 'A', 'text' => 'Parâmetro é na definição da função, argumento é na chamada'],
                    ['id' => 'B', 'text' => 'São a mesma coisa'],
                    ['id' => 'C', 'text' => 'Argumento é na definição, parâmetro na chamada'],
                    ['id' => 'D', 'text' => 'Parâmetros são opcionais, argumentos obrigatórios']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Parâmetros são variáveis na definição da função, argumentos são os valores passados na chamada.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ],
            [
                'statement' => 'O que é debugging?',
                'options' => [
                    ['id' => 'A', 'text' => 'Processo de encontrar e corrigir erros no código'],
                    ['id' => 'B', 'text' => 'Compilar o programa'],
                    ['id' => 'C', 'text' => 'Otimizar o código'],
                    ['id' => 'D', 'text' => 'Documentar o código']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Debugging é o processo sistemático de identificar, analisar e corrigir bugs (erros) no código.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'easy'
            ],
            [
                'statement' => 'Qual é a principal vantagem da modularização?',
                'options' => [
                    ['id' => 'A', 'text' => 'Facilita manutenção e reutilização de código'],
                    ['id' => 'B', 'text' => 'Torna o programa mais rápido'],
                    ['id' => 'C', 'text' => 'Reduz o tamanho do arquivo'],
                    ['id' => 'D', 'text' => 'Elimina todos os bugs']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Modularização divide o código em módulos menores, facilitando manutenção, teste e reutilização.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ],
            [
                'statement' => 'O que é uma API?',
                'options' => [
                    ['id' => 'A', 'text' => 'Interface de Programação de Aplicações'],
                    ['id' => 'B', 'text' => 'Um tipo de banco de dados'],
                    ['id' => 'C', 'text' => 'Uma linguagem de programação'],
                    ['id' => 'D', 'text' => 'Um sistema operacional']
                ],
                'correct_answer' => 'A',
                'explanation' => 'API (Application Programming Interface) define como diferentes componentes de software devem interagir.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ],
            [
                'statement' => 'Qual é a diferença entre sintaxe e semântica?',
                'options' => [
                    ['id' => 'A', 'text' => 'Sintaxe são as regras de escrita, semântica é o significado'],
                    ['id' => 'B', 'text' => 'São a mesma coisa'],
                    ['id' => 'C', 'text' => 'Semântica são as regras, sintaxe é o significado'],
                    ['id' => 'D', 'text' => 'Sintaxe é para humanos, semântica para máquinas']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Sintaxe refere-se às regras de estrutura da linguagem, semântica ao significado das instruções.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ],
            [
                'statement' => 'O que é um identificador em programação?',
                'options' => [
                    ['id' => 'A', 'text' => 'Nome dado a variáveis, funções ou classes'],
                    ['id' => 'B', 'text' => 'Um número único'],
                    ['id' => 'C', 'text' => 'Um tipo de dado'],
                    ['id' => 'D', 'text' => 'Um operador matemático']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Identificadores são nomes usados para referenciar variáveis, funções, classes e outros elementos do código.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'easy'
            ],
            [
                'statement' => 'Qual é o propósito dos comentários no código?',
                'options' => [
                    ['id' => 'A', 'text' => 'Documentar e explicar o código'],
                    ['id' => 'B', 'text' => 'Acelerar a execução'],
                    ['id' => 'C', 'text' => 'Corrigir erros automaticamente'],
                    ['id' => 'D', 'text' => 'Compilar o programa']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Comentários servem para documentar o código, explicando sua funcionalidade para outros desenvolvedores.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'easy'
            ],
            [
                'statement' => 'O que é polimorfismo?',
                'options' => [
                    ['id' => 'A', 'text' => 'Capacidade de um objeto assumir múltiplas formas'],
                    ['id' => 'B', 'text' => 'Um tipo de herança'],
                    ['id' => 'C', 'text' => 'Uma estrutura de dados'],
                    ['id' => 'D', 'text' => 'Um padrão de design']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Polimorfismo permite que objetos de diferentes classes sejam tratados de forma uniforme através de uma interface comum.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'hard'
            ],
            [
                'statement' => 'Qual é a diferença entre compilação e interpretação?',
                'options' => [
                    ['id' => 'A', 'text' => 'Compilação traduz todo o código antes da execução, interpretação traduz linha por linha'],
                    ['id' => 'B', 'text' => 'Interpretação é mais rápida que compilação'],
                    ['id' => 'C', 'text' => 'Compilação é feita em tempo de execução'],
                    ['id' => 'D', 'text' => 'Não há diferença entre elas']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Compiladores traduzem todo o código fonte antes da execução, interpretadores traduzem e executam linha por linha.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ],
            [
                'statement' => 'O que é encapsulamento?',
                'options' => [
                    ['id' => 'A', 'text' => 'Ocultar detalhes internos de implementação'],
                    ['id' => 'B', 'text' => 'Criar múltiplas instâncias'],
                    ['id' => 'C', 'text' => 'Herdar características de outra classe'],
                    ['id' => 'D', 'text' => 'Executar múltiplas tarefas simultaneamente']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Encapsulamento é o princípio de ocultar os detalhes internos de implementação, expondo apenas uma interface pública.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ],
            [
                'statement' => 'Qual é a função do garbage collector?',
                'options' => [
                    ['id' => 'A', 'text' => 'Liberar automaticamente memória não utilizada'],
                    ['id' => 'B', 'text' => 'Compilar o código'],
                    ['id' => 'C', 'text' => 'Detectar erros de sintaxe'],
                    ['id' => 'D', 'text' => 'Otimizar o desempenho']
                ],
                'correct_answer' => 'A',
                'explanation' => 'O garbage collector gerencia automaticamente a memória, liberando espaço ocupado por objetos que não são mais referenciados.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ],
            [
                'statement' => 'O que é um framework?',
                'options' => [
                    ['id' => 'A', 'text' => 'Uma estrutura que fornece funcionalidades básicas para desenvolvimento'],
                    ['id' => 'B', 'text' => 'Um tipo de banco de dados'],
                    ['id' => 'C', 'text' => 'Uma linguagem de programação'],
                    ['id' => 'D', 'text' => 'Um sistema operacional']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Um framework é uma estrutura de software que fornece funcionalidades básicas e padrões para facilitar o desenvolvimento.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ],
            [
                'statement' => 'Qual é a importância dos testes unitários?',
                'options' => [
                    ['id' => 'A', 'text' => 'Verificar se cada componente funciona corretamente de forma isolada'],
                    ['id' => 'B', 'text' => 'Acelerar a compilação'],
                    ['id' => 'C', 'text' => 'Reduzir o tamanho do código'],
                    ['id' => 'D', 'text' => 'Melhorar a interface do usuário']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Testes unitários verificam se cada unidade de código (função, método, classe) funciona corretamente de forma isolada.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ],
            [
                'statement' => 'O que é refatoração de código?',
                'options' => [
                    ['id' => 'A', 'text' => 'Melhorar a estrutura do código sem alterar sua funcionalidade'],
                    ['id' => 'B', 'text' => 'Adicionar novas funcionalidades'],
                    ['id' => 'C', 'text' => 'Corrigir bugs'],
                    ['id' => 'D', 'text' => 'Documentar o código']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Refatoração é o processo de reestruturar código existente para melhorar sua legibilidade e manutenibilidade sem alterar seu comportamento.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ]
        ];
        
        $allQuestions = array_merge($questions, $moreQuestions);
        
        foreach ($allQuestions as $index => $questionData) {
            SimuladoQuestion::create([
                'simulado_id' => $simulado->id,
                'statement' => $questionData['statement'],
                'q_type' => $questionData['q_type'],
                'weight' => $questionData['weight'],
                'difficulty' => $questionData['difficulty'],
                'options' => json_encode($questionData['options']),
                'correct_answer' => $questionData['correct_answer'],
                'explanation' => $questionData['explanation'],
                'q_order' => $index + 1,
            ]);
        }
    }
    
    private function createSimuladoEstruturasData()
    {
        $category = SimuladoCategory::where('name', 'Estruturas de Dados')->first();
        
        $simulado = Simulado::create([
            'title' => 'Estruturas de Dados',
            'description' => 'Avalie seus conhecimentos sobre arrays, listas, pilhas, filas, árvores e outras estruturas fundamentais.',
            'category_id' => $category->id,
            'duration' => 4500, // 75 minutos
            'min_score' => 75,
            'max_attempts' => 2,
            'type' => 'exam',
            'allow_navigation' => true,
            'allow_save_progress' => true,
            'show_feedback' => true,
            'status' => 'active',
        ]);
        
        $questions = [
            [
                'statement' => 'Qual é a principal característica de uma pilha (stack)?',
                'options' => [
                    ['id' => 'A', 'text' => 'LIFO - Last In, First Out'],
                    ['id' => 'B', 'text' => 'FIFO - First In, First Out'],
                    ['id' => 'C', 'text' => 'Acesso aleatório aos elementos'],
                    ['id' => 'D', 'text' => 'Ordenação automática']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Uma pilha segue o princípio LIFO, onde o último elemento inserido é o primeiro a ser removido.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'easy'
            ],
            [
                'statement' => 'Em uma fila (queue), qual princípio é seguido?',
                'options' => [
                    ['id' => 'A', 'text' => 'LIFO - Last In, First Out'],
                    ['id' => 'B', 'text' => 'FIFO - First In, First Out'],
                    ['id' => 'C', 'text' => 'Acesso direto por índice'],
                    ['id' => 'D', 'text' => 'Ordenação por prioridade']
                ],
                'correct_answer' => 'B',
                'explanation' => 'Uma fila segue o princípio FIFO, onde o primeiro elemento inserido é o primeiro a ser removido.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'easy'
            ],
            [
                'statement' => 'Qual é a complexidade de tempo para acessar um elemento em um array por índice?',
                'options' => [
                    ['id' => 'A', 'text' => 'O(1)'],
                    ['id' => 'B', 'text' => 'O(n)'],
                    ['id' => 'C', 'text' => 'O(log n)'],
                    ['id' => 'D', 'text' => 'O(n²)']
                ],
                'correct_answer' => 'A',
                'explanation' => 'O acesso a um elemento de array por índice é O(1) - tempo constante, independente do tamanho do array.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ]
        ];
        
        // Adicionar mais questões para completar 25
        $moreQuestions = [];
        for ($i = 4; $i <= 25; $i++) {
            $moreQuestions[] = [
                'statement' => "Questão $i sobre estruturas de dados - exemplo genérico",
                'options' => [
                    ['id' => 'A', 'text' => 'Opção A'],
                    ['id' => 'B', 'text' => 'Opção B'],
                    ['id' => 'C', 'text' => 'Opção C'],
                    ['id' => 'D', 'text' => 'Opção D']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Explicação da questão sobre estruturas de dados.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ];
        }
        
        $allQuestions = array_merge($questions, $moreQuestions);
        
        foreach ($allQuestions as $index => $questionData) {
            SimuladoQuestion::create([
                'simulado_id' => $simulado->id,
                'statement' => $questionData['statement'],
                'q_type' => $questionData['q_type'],
                'weight' => $questionData['weight'],
                'difficulty' => $questionData['difficulty'],
                'options' => json_encode($questionData['options']),
                'correct_answer' => $questionData['correct_answer'],
                'explanation' => $questionData['explanation'],
                'q_order' => $index + 1,
            ]);
        }
    }
    
    private function createSimuladoAlgoritmos()
    {
        $category = SimuladoCategory::where('name', 'Algoritmos')->first();
        
        $simulado = Simulado::create([
            'title' => 'Algoritmos e Complexidade',
            'description' => 'Teste seus conhecimentos sobre algoritmos de ordenação, busca, análise de complexidade e otimização.',
            'category_id' => $category->id,
            'duration' => 5400, // 90 minutos
            'min_score' => 80,
            'max_attempts' => 2,
            'type' => 'exam',
            'allow_navigation' => true,
            'allow_save_progress' => true,
            'show_feedback' => true,
            'status' => 'active',
        ]);
        
        $questions = [];
        for ($i = 1; $i <= 30; $i++) {
            $questions[] = [
                'statement' => "Questão $i sobre algoritmos e complexidade - exemplo",
                'options' => [
                    ['id' => 'A', 'text' => 'Opção A'],
                    ['id' => 'B', 'text' => 'Opção B'],
                    ['id' => 'C', 'text' => 'Opção C'],
                    ['id' => 'D', 'text' => 'Opção D']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Explicação da questão sobre algoritmos.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'hard'
            ];
        }
        
        foreach ($questions as $index => $questionData) {
            SimuladoQuestion::create([
                'simulado_id' => $simulado->id,
                'statement' => $questionData['statement'],
                'q_type' => $questionData['q_type'],
                'weight' => $questionData['weight'],
                'difficulty' => $questionData['difficulty'],
                'options' => json_encode($questionData['options']),
                'correct_answer' => $questionData['correct_answer'],
                'explanation' => $questionData['explanation'],
                'q_order' => $index + 1,
            ]);
        }
    }
    
    private function createSimuladoWeb()
    {
        $category = SimuladoCategory::where('name', 'Desenvolvimento Web')->first();
        
        $simulado = Simulado::create([
            'title' => 'Desenvolvimento Web',
            'description' => 'Avalie seus conhecimentos sobre HTML, CSS, JavaScript, frameworks web e tecnologias relacionadas.',
            'category_id' => $category->id,
            'duration' => 6300, // 105 minutos
            'min_score' => 70,
            'max_attempts' => 3,
            'type' => 'exam',
            'allow_navigation' => true,
            'allow_save_progress' => true,
            'show_feedback' => true,
            'status' => 'active',
        ]);
        
        $questions = [];
        for ($i = 1; $i <= 35; $i++) {
            $questions[] = [
                'statement' => "Questão $i sobre desenvolvimento web - exemplo",
                'options' => [
                    ['id' => 'A', 'text' => 'Opção A'],
                    ['id' => 'B', 'text' => 'Opção B'],
                    ['id' => 'C', 'text' => 'Opção C'],
                    ['id' => 'D', 'text' => 'Opção D']
                ],
                'correct_answer' => 'A',
                'explanation' => 'Explicação da questão sobre desenvolvimento web.',
                'q_type' => 'multiple_choice',
                'weight' => 1,
                'difficulty' => 'medium'
            ];
        }
        
        foreach ($questions as $index => $questionData) {
            SimuladoQuestion::create([
                'simulado_id' => $simulado->id,
                'statement' => $questionData['statement'],
                'q_type' => $questionData['q_type'],
                'weight' => $questionData['weight'],
                'difficulty' => $questionData['difficulty'],
                'options' => json_encode($questionData['options']),
                'correct_answer' => $questionData['correct_answer'],
                'explanation' => $questionData['explanation'],
                'q_order' => $index + 1,
            ]);
        }
    }
}