<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Uspdev\Forms\Models\FormDefinition;
use Uspdev\Forms\Services\FormDefinitionService;
use Uspdev\Workflow\Models\WorkflowDefinition;



class CreateWorkflowDemo extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $workflow_demo = Request::create('/workflow/demo', 'POST', [
            'name' => 'emprestimo_livro_simples',
            'description' => 'Empréstimo de Livro',
            'definition' => '
            {
                "name": "emprestimo_livro_simples",
                "label": "Empréstimo de Livro",
                "initial_places": [
                    "pedido"
                ],
                "roles": [
                    {
                        "name": "usuario",
                        "label": "Usuário",
                        "source": "*senhaunica.Alunogr"
                    },
                    {
                        "name": "bibliotecario",
                        "label": "Bibliotecário"
                    }
                ],
                "places": [
                    {
                        "name": "pedido",
                        "label": "Enviar pedido",
                        "roles": [
                            "usuario"
                        ]
                    },
                    {
                        "name": "analise",
                        "label": "Em análise",
                        "roles": [
                            "bibliotecario"
                        ]
                    },
                    {
                        "name": "retirada",
                        "label": "Aguardando retirada",
                        "roles": [
                            "bibliotecario"
                        ]
                    },
                    {
                        "name": "aviso_prazo",
                        "label": "Ciência do prazo de devolução",
                        "roles": [
                            "usuario"
                        ]
                    },
                    {
                        "name": "confirma_pedido",
                        "label": "Confirmação do pedido",
                        "roles": [
                            "usuario"
                        ]
                    },
                    {
                        "name": "emprestado",
                        "label": "Livro emprestado",
                        "roles": [
                            "usuario"
                        ]
                    },
                    {
                        "name": "conferencia",
                        "label": "Conferência da devolução",
                        "roles": [
                            "bibliotecario"
                        ]
                    },
                    {
                        "name": "multa",
                        "label": "Multa pendente",
                        "roles": [
                            "usuario"
                        ]
                    },
                    {
                        "name": "finalizado",
                        "label": "Finalizado",
                        "roles": [
                            "usuario"
                        ]
                    }
                ],
                "transitions": [
                    {
                        "name": "tr_enviar",
                        "label": "Enviar para análise",
                        "from": "pedido",
                        "tos": [
                            "analise"
                        ]
                    },
                    {
                        "name": "tr_aprovar",
                        "label": "Aprovar empréstimo",
                        "from": "analise",
                        "tos": [
                            "confirma_pedido",
                            "aviso_prazo"
                        ],
                        "form": false,
                        "notifications": {
                            "append_roles": [
                                "usuario"
                            ]
                        }
                    },
                    {
                        "name": "tr_rejeitar",
                        "label": "Solicitar correção",
                        "from": "analise",
                        "tos": [
                            "pedido"
                        ],
                        "form": "rejeitar_pedido",
                        "bindings": [
                            {
                                "attribute": "bibliotecario_rej",
                                "from": "form.user_codpes",
                                "resolver": "user_by_codpes"
                            }
                        ],
                        "notifications": {
                            "append_roles": [
                                "usuario"
                            ]
                        }
                    },
                    {
                        "name": "tr_negar",
                        "label": "Negar empréstimo",
                        "from": "analise",
                        "tos": [
                            "finalizado"
                        ],
                        "form": "rejeitar_pedido",
                        "bindings": [
                            {
                                "attribute": "bibliotecario_neg",
                                "from": "form.user_codpes",
                                "resolver": "user_by_codpes"
                            }
                        ],
                        "notifications": {
                            "append_roles": [
                                "usuario"
                            ]
                        }
                    },
                    {
                        "name": "tr_confirma_pedido",
                        "label": "Confirmar pedido",
                        "from": "confirma_pedido",
                        "tos": [
                            "retirada"
                        ],
                        "notifications": {
                            "append_roles": [
                                "bibliotecario"
                            ]
                        }
                    },
                    {
                        "name": "tr_ciente",
                        "label": "Estou ciente do prazo",
                        "from": "aviso_prazo",
                        "tos": [
                            "retirada"
                        ]
                    },
                    {
                        "name": "tr_retirar",
                        "label": "Registrar retirada",
                        "from": "retirada",
                        "tos": [
                            "emprestado"
                        ],
                        "form": "blame_bibliotecario",
                        "bindings": [
                            {
                                "attribute": "bibliotecario_ret",
                                "from": "form.user_codpes",
                                "resolver": "user_by_codpes"
                            }
                        ]
                    },
                    {
                        "name": "tr_devolver",
                        "label": "Devolver livro",
                        "from": "emprestado",
                        "tos": [
                            "conferencia"
                        ],
                        "notifications": {
                            "append_roles": [
                                "bibliotecario"
                            ]
                        }
                    },
                    {
                        "name": "tr_conferir_ok",
                        "label": "Devolução em dia",
                        "from": "conferencia",
                        "tos": [
                            "finalizado"
                        ],
                        "form": "blame_bibliotecario",
                        "bindings": [
                            {
                                "attribute": "bibliotecario_conf",
                                "from": "form.user_codpes",
                                "resolver": "user_by_codpes"
                            }
                        ],
                        "notifications": {
                            "append_roles": [
                                "usuario"
                            ]
                        }
                    },
                    {
                        "name": "tr_conferir_atraso",
                        "label": "Devolução com atraso",
                        "from": "conferencia",
                        "tos": [
                            "multa"
                        ],
                        "form": "blame_bibliotecario",
                        "bindings": [
                            {
                                "attribute": "bibliotecario_conf",
                                "from": "form.user_codpes",
                                "resolver": "user_by_codpes"
                            }
                        ],
                        "notifications": {
                            "append_roles": [
                                "usuario"
                            ]
                        }
                    },
                    {
                        "name": "tr_pagar_multa",
                        "label": "Registrar pagamento",
                        "from": "multa",
                        "tos": [
                            "finalizado"
                        ],
                        "notifications": {
                            "append_roles": [
                                "bibliotecario"
                            ]
                        }
                    }
                ]
            }',]);

       $rej_ped_form =  Request::create('/workflow/formdemo', 'POST', [
            'name' => 'rejeitar_pedido',
            'version' => 1,
            'group' => 'workflow',
            'description' => 'Formulário de rejeição de pedido de empréstimo de livro',
            'fields' => '[ 
                { 
                    "name": "user_codpes", 
                    "type": "pessoa-usp", 
                    "label": "Código do Bibliotecário", 
                    "required": true
                }, 
                [ 
                    { 
                        "name": "justificativa", 
                        "type": "textarea", 
                        "label": "Justificativa da rejeição", 
                        "required": true, 
                        "validation_rule": "min:20", 
                        "width": 8 
                    }, 
                    { 
                        "name": "data_parecer", 
                        "type": "date", 
                        "label": "Data do parecer", 
                        "required": true 
                    } 
                ] 
            ]'
        ]);
        
        $blame_form =  Request::create('/workflow/formdemo', 'POST', [
            'name' => 'blame_bibliotecario',
            'version' => 1,
            'group' => 'workflow',
            'description' => 'Formulário de registro de bibliotecário responsável por uma ação',
            'fields' => '[ 
                [
                    { 
                        "name": "user_codpes", 
                        "type": "pessoa-usp", 
                        "label": "Código do Bibliotecário", 
                        "required": true
                    },
                    { 
                        "name": "observacao", 
                        "type": "text", 
                        "label": "Observação"
                    }
                ] 
            ]'
        ]);

        $workflow_def = WorkflowDefinition::storeDefinition($workflow_demo);
        $workflow_def->publish();
        app(FormDefinitionService::class)->createFromRequest($rej_ped_form);
        app(FormDefinitionService::class)->createFromRequest($blame_form);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $workflow_def = WorkflowDefinition::where('name', 'emprestimo_livro_simples')->first();
        $workflow_def->delete();

        $rej_ped_form = FormDefinition::where('name', 'rejeitar_pedido')->first();
        $rej_ped_form->delete();

        $blame_form = FormDefinition::where('name', 'blame_bibliotecario')->first();
        $blame_form->delete();
    }
};
