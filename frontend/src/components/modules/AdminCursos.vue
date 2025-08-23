<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Gestão de Cursos</h2>
      <p class="text-gray-600">Crie e gira cursos de formação para os utilizadores</p>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 space-y-4">
      <!-- Top row: New Course button and filters -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div class="flex-shrink-0">
          <button @click="openWizardNew" class="btn-primary w-full sm:w-auto">
            + Novo Curso
          </button>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
          <select v-model="filterCategory" class="form-input w-full sm:w-48">
            <option value="">Todas as Categorias</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.name">{{ cat.name }}</option>
          </select>
          <input 
            v-model="searchTerm" 
            type="text" 
            placeholder="Procurar cursos..."
            class="form-input w-full sm:w-64"
          >
        </div>
      </div>
      
      <!-- Bottom row: Items per page selector and pagination info -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0 px-2 sm:px-4 py-3 bg-gray-50 rounded-lg">
        <div class="text-sm text-gray-600 order-2 sm:order-1">
          Página {{ page }} de {{ Math.max(1, Math.ceil(total / perPage)) }} · {{ total }} resultados
        </div>
        <div class="flex items-center space-x-3 order-1 sm:order-2">
          <label class="text-sm text-gray-600 whitespace-nowrap">Por página</label>
          <select v-model.number="perPage" @change="page = 1; loadCursos()" class="form-input h-8 w-20">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="mb-4 p-3 border border-red-200 bg-red-50 text-red-700 rounded">
      {{ error }}
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="mb-4 p-3 border border-gray-200 bg-gray-50 text-gray-700 rounded">
      A carregar cursos...
    </div>

    <!-- Empty State -->
    <div v-else-if="!filteredCursos.length" class="card">
      <div class="p-6 text-center text-gray-600">
        Nenhum curso encontrado.
      </div>
    </div>

    <!-- Courses Table -->
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto overflow-y-hidden">
        <table class="min-w-full table-auto">
          <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <tr>
              <th @click="changeSort('titulo')" class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100 transition-colors duration-200 min-w-0">
                <div class="flex items-center space-x-1">
                  <span class="truncate">Curso</span>
                  <span v-if="sortBy==='titulo'" class="text-blue-500 flex-shrink-0">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
                </div>
              </th>

              <th @click="changeSort('inscritos')" class="hidden md:table-cell px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100 transition-colors duration-200">
                <div class="flex items-center space-x-1">
                  <span>Inscritos</span>
                  <span v-if="sortBy==='inscritos'" class="text-blue-500">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
                </div>
              </th>

              <th @click="changeSort('avaliacao')" class="hidden xl:table-cell px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100 transition-colors duration-200">
                <div class="flex items-center space-x-1">
                  <span>Avaliação</span>
                  <span v-if="sortBy==='avaliacao'" class="text-blue-500">{{ sortDir==='asc' ? '▲' : '▼' }}</span>
                </div>
              </th>
              <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
              <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="curso in filteredCursos" :key="curso.id" class="hover:bg-gray-50 transition-colors duration-200">
              <td class="px-3 sm:px-6 py-4 min-w-0">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 sm:h-12 sm:w-12">
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-600 flex items-center justify-center shadow-lg">
                      <span class="text-white font-bold text-sm sm:text-lg">{{ (curso.titulo || curso.title || '?').charAt(0).toUpperCase() }}</span>
                    </div>
                  </div>
                  <div class="ml-3 sm:ml-4 min-w-0 flex-1">
                    <div class="text-sm font-semibold text-gray-900 mb-1 truncate">{{ curso.titulo || curso.title }}</div>
                    <div class="text-xs sm:text-sm text-gray-500 truncate">{{ curso.descricao || curso.description || curso.instrutor || 'Sem descrição' }}</div>
                  </div>
                </div>
              </td>

              <td class="hidden md:table-cell px-3 sm:px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ curso.inscritos || 0 }}</div>
              </td>

              <td class="hidden xl:table-cell px-3 sm:px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex">
                    <svg v-for="i in 5" :key="i" class="w-3 h-3 sm:w-4 sm:h-4" :class="i <= (curso.avaliacao || 0) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                  </div>
                  <span class="ml-1 sm:ml-2 text-xs sm:text-sm text-gray-600">({{ curso.totalAvaliacoes || 0 }})</span>
                </div>
              </td>
              <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(curso.status)" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ displayStatus(curso.status) }}
                </span>
              </td>
              <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex flex-col sm:flex-row space-y-1 sm:space-y-0 sm:space-x-2">
                  <button @click="editCurso(curso)" class="inline-flex items-center justify-center px-2 py-1 sm:px-3 sm:py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span class="hidden sm:inline">Editar</span>
                  </button>
                  <button @click="deleteCurso(curso.id)" class="inline-flex items-center justify-center px-2 py-1 sm:px-3 sm:py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span class="hidden sm:inline">Eliminar</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination Controls -->
    <div class="flex items-center justify-center space-x-4 mt-6">
      <button 
        :disabled="page <= 1" 
        @click="changePage(page - 1)"
        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Anterior
      </button>
      
      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-600">
          Página {{ page }} de {{ Math.max(1, Math.ceil(total / perPage)) }}
        </span>
      </div>
      
      <button 
        :disabled="page >= Math.ceil(total / perPage)" 
        @click="changePage(page+1)"
        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-sm"
      >
        Seguinte
        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>
    </div>

    <!-- Create/Edit Course Modal as Wizard -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-60 h-full w-full z-50 flex items-center justify-center p-4">
      <div class="relative w-full max-w-5xl bg-white rounded-2xl shadow-2xl h-[90vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6 text-white">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-2xl font-bold">{{ isEditing ? 'Editar Curso' : 'Criar Novo Curso' }}</h3>
              <p class="text-blue-100 mt-1">Configure seu curso passo a passo</p>
            </div>
            <button @click="closeWizard" class="text-white hover:text-gray-200 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Progress Steps -->
        <div class="bg-gray-50 px-8 py-6 border-b">
          <div class="flex items-center justify-between">
            <template v-for="s in [1,2,3,4,5]" :key="'step-'+s">
              <div class="flex items-center">
                <div class="flex flex-col items-center">
                  <div :class="[
                    'w-12 h-12 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300',
                    wizardStep >= s 
                      ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg transform scale-110' 
                      : wizardStep === s - 1 
                        ? 'bg-blue-100 text-blue-600 border-2 border-blue-300'
                        : 'bg-gray-200 text-gray-500'
                  ]">
                    <svg v-if="wizardStep > s" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span v-else>{{ s }}</span>
                  </div>
                  <div class="mt-2 text-xs font-medium" :class="wizardStep >= s ? 'text-blue-600' : 'text-gray-500'">
                    {{ ['Básico', 'Detalhes', 'Mídia', 'Conteúdo', 'Publicar'][s-1] }}
                  </div>
                </div>
                <div v-if="s < 5" class="flex-1 mx-4">
                  <div class="h-1 rounded-full transition-all duration-300" :class="wizardStep > s ? 'bg-gradient-to-r from-blue-500 to-purple-500' : 'bg-gray-200'"></div>
                </div>
              </div>
            </template>
          </div>
        </div>

        <!-- Modal Content -->
        <div class="flex-1 px-8 py-6 overflow-y-auto">

          <!-- Step 1: Nome Básico -->
          <div v-if="wizardStep === 1" class="space-y-8">
            <div class="text-center">
              <div class="mx-auto w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
              <h4 class="text-xl font-semibold text-gray-900 mb-2">Vamos começar com o básico</h4>
              <p class="text-gray-600">Dê um nome ao seu curso que seja claro e atrativo para os alunos</p>
            </div>

            <div class="max-w-2xl mx-auto">
              <div class="space-y-6">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-3">
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd"></path>
                      </svg>
                      Título do Curso *
                    </span>
                  </label>
                  <input 
                    v-model="newCurso.titulo" 
                    type="text" 
                    required 
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-0 transition-colors text-lg"
                    placeholder="Ex.: Fundamentos de Segurança no Trabalho"
                    maxlength="100"
                  >
                  <div class="mt-2 flex justify-between items-center">
                    <p class="text-sm text-gray-500">Escolha um título claro e descritivo</p>
                    <span class="text-xs text-gray-400">{{ newCurso.titulo?.length || 0 }}/100</span>
                  </div>
                </div>

                <!-- Botões de Navegação Step 1 -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                  <div></div> <!-- Espaço vazio para alinhamento -->
                  
                  <div class="flex space-x-3">
                    <button type="button" 
                            class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200" 
                            @click="closeWizard">
                      Cancelar
                    </button>
                    <button type="button" 
                            :disabled="!newCurso.titulo" 
                            :class="[
                              'inline-flex items-center px-6 py-3 shadow-sm text-sm font-medium rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200',
                              newCurso.titulo 
                                ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700 focus:ring-blue-500' 
                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                            ]" 
                            @click="wizardStep = 2">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                      </svg>
                      Próximo
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 2: Informações -->
          <div v-if="wizardStep === 2" class="space-y-8">
            <div class="text-center">
              <div class="mx-auto w-16 h-16 bg-gradient-to-r from-green-500 to-blue-500 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              <h4 class="text-xl font-semibold text-gray-900 mb-2">Detalhes do Curso</h4>
              <p class="text-gray-600">Adicione informações importantes sobre o conteúdo e organização</p>
            </div>

            <div class="max-w-4xl mx-auto">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Coluna Esquerda - Descrição -->
                <div class="space-y-6">
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                      <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                        </svg>
                        Descrição do Curso
                      </span>
                    </label>
                    <textarea 
                      v-model="newCurso.descricao" 
                      rows="8" 
                      class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-0 transition-colors resize-none"
                      placeholder="Descreva o que os alunos irão aprender, os objetivos do curso e os benefícios..."
                      maxlength="500"
                    ></textarea>
                    <div class="mt-2 flex justify-between items-center">
                      <p class="text-sm text-gray-500">Seja claro sobre os objetivos e benefícios</p>
                      <span class="text-xs text-gray-400">{{ newCurso.descricao?.length || 0 }}/500</span>
                    </div>
                  </div>
                </div>

                <!-- Coluna Direita - Configurações -->
                <div class="space-y-6">
                  <div class="bg-white border-2 border-gray-100 rounded-xl p-6 space-y-6">
                    <h5 class="font-semibold text-gray-900 flex items-center">
                      <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                      Configurações
                    </h5>

                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Status do Curso</label>
                      <select v-model="newCurso.status" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-0 transition-colors">
                        <option value="rascunho">📝 Rascunho</option>
                        <option value="publicado">✅ Publicado</option>
                        <option value="arquivado">📦 Arquivado</option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Categoria</label>
                      <select v-model="newCurso.categoria" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-0 transition-colors">
                        <option value="">Selecionar categoria</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.name">{{ cat.name }}</option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-sm font-semibold text-gray-700 mb-2">Instrutor</label>
                      <input 
                        v-model="newCurso.instrutor" 
                        type="text" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-0 transition-colors"
                        placeholder="Nome do instrutor responsável"
                      >
                    </div>
                  </div>
                </div>

                <!-- Botões de Navegação Step 2 -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                  <button type="button" 
                          class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200" 
                          @click="wizardStep = 1">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Anterior
                  </button>
                  
                  <div class="flex space-x-3">
                    <button type="button" 
                            class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200" 
                            @click="closeWizard">
                      Cancelar
                    </button>
                    <button type="button" 
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-sm text-sm font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200" 
                            @click="wizardStep = 3">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                      </svg>
                      Próximo
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 3: Mídia (Thumbnail) -->
          <div v-if="wizardStep === 3" class="space-y-8">
            <div class="text-center">
              <div class="mx-auto w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
              <h4 class="text-xl font-semibold text-gray-900 mb-2">Imagem do Curso</h4>
              <p class="text-gray-600">Adicione uma imagem atrativa que represente o conteúdo do curso</p>
            </div>

            <div class="max-w-2xl mx-auto">
              <div v-if="!thumbPreviewUrl" class="relative">
                <div class="border-3 border-dashed border-gray-300 rounded-2xl p-12 text-center hover:border-purple-400 transition-colors group cursor-pointer" @click="onPickThumb" :class="{ 'pointer-events-none opacity-50': thumbUploading }">
                  <div class="space-y-4">
                    <div class="mx-auto w-20 h-20 bg-gradient-to-r from-purple-100 to-pink-100 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                      <svg v-if="!thumbUploading" class="w-10 h-10 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                      <div v-else class="animate-spin rounded-full h-10 w-10 border-b-2 border-purple-500"></div>
                    </div>
                    <div>
                      <h5 class="text-lg font-semibold text-gray-900 mb-2">{{ thumbUploading ? 'Carregando...' : 'Carregar Imagem' }}</h5>
                      <p class="text-gray-600 mb-4">{{ thumbUploading ? 'Aguarde enquanto processamos sua imagem' : 'Clique para selecionar uma imagem do seu dispositivo' }}</p>
                      <div v-if="!thumbUploading" class="inline-flex items-center px-4 py-2 bg-purple-50 text-purple-700 rounded-lg text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Escolher Arquivo
                      </div>
                    </div>
                    <div class="text-xs text-gray-500 space-y-1">
                      <p>Formatos aceitos: PNG, JPG, JPEG, WebP</p>
                      <p>Tamanho máximo: 5MB</p>
                      <p>Resolução recomendada: 1280x720px</p>
                    </div>
                  </div>
                </div>
                
                <!-- Progress Bar -->
                <div v-if="thumbUploading" class="mt-4">
                  <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full transition-all duration-300" :style="{ width: thumbProgress + '%' }"></div>
                  </div>
                  <p class="text-center text-sm text-gray-600 mt-2">{{ thumbProgress }}% concluído</p>
                </div>
                
                <!-- Error Message -->
                <div v-if="thumbError" class="mt-4 bg-red-50 border border-red-200 rounded-xl p-4">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                      <p class="text-sm font-medium text-red-800">Erro no upload</p>
                      <p class="text-xs text-red-600">{{ thumbError }}</p>
                    </div>
                  </div>
                </div>
                
                <input ref="thumbInput" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="onThumbSelected">
              </div>

              <div v-else class="space-y-6">
                <div class="relative group">
                  <div class="relative overflow-hidden rounded-2xl shadow-xl">
                    <img :src="thumbPreviewUrl" alt="Thumbnail do curso" class="w-full h-64 object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center">
                      <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 space-x-3">
                        <button 
                          type="button" 
                          class="bg-white text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors"
                          @click="onPickThumb"
                          :disabled="thumbUploading"
                        >
                          Alterar
                        </button>
                        <button 
                          type="button" 
                          class="bg-red-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-600 transition-colors"
                          @click="thumbPreviewUrl = null; thumbFile = null"
                          :disabled="thumbUploading"
                        >
                          Remover
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                      <p class="text-sm font-medium text-green-800">Imagem carregada com sucesso!</p>
                      <p class="text-xs text-green-600">{{ thumbFile ? thumbFile.name : 'Esta imagem será exibida como capa do curso' }}</p>
                    </div>
                  </div>
                </div>
                
                <input ref="thumbInput" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="onThumbSelected">
              </div>

              <!-- Botões de Navegação Step 3 -->
              <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                <button type="button" 
                        class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200" 
                        @click="wizardStep = 2">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                  </svg>
                  Anterior
                </button>
                
                <div class="flex space-x-3">
                  <button type="button" 
                          class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200" 
                          @click="closeWizard">
                    Cancelar
                  </button>
                  <button type="button" 
                          class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-sm text-sm font-medium rounded-xl hover:from-purple-700 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200" 
                          @click="wizardStep = 4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    Próximo
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 4: Currículo (Módulos e Aulas) -->
          <div v-if="wizardStep === 4" class="space-y-8">
            <div class="text-center">
              <div class="mx-auto w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
              </div>
              <h4 class="text-xl font-semibold text-gray-900 mb-2">Estrutura do Curso</h4>
              <p class="text-gray-600">Organize o conteúdo em módulos e aulas para uma melhor experiência de aprendizado</p>
            </div>

            <div class="max-w-7xl mx-auto">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Coluna Esquerda - Módulos -->
                <div class="space-y-6">
                  <div class="bg-white border-2 border-gray-100 rounded-2xl p-6">
                    <div class="flex items-center mb-6">
                      <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                      </div>
                      <h5 class="text-lg font-semibold text-gray-900">Módulos do Curso</h5>
                    </div>

                    <!-- Formulário de Novo Módulo -->
                    <div class="space-y-4 mb-6">
                      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <input 
                          v-model="moduleForm.title" 
                          class="col-span-2 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-0 transition-colors" 
                          placeholder="Título do módulo"
                        >
                        <input 
                          v-model.number="moduleForm.sort_index" 
                          class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-0 transition-colors" 
                          type="number" 
                          min="0" 
                          placeholder="Ordem"
                        >
                      </div>
                      <textarea 
                        v-model="moduleForm.description" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-0 transition-colors resize-none" 
                        rows="2" 
                        placeholder="Descrição do módulo (opcional)"
                      ></textarea>
                      <button 
                        class="w-full bg-indigo-600 text-white px-4 py-3 rounded-xl font-medium hover:bg-indigo-700 transition-colors flex items-center justify-center" 
                        @click="createModule"
                      >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Adicionar Módulo
                      </button>
                    </div>

                    <!-- Lista de Módulos -->
                    <div class="space-y-3">
                      <div v-if="courseModules.length === 0" class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <p class="text-sm">Nenhum módulo criado ainda</p>
                        <p class="text-xs">Adicione módulos para organizar o conteúdo</p>
                      </div>
                      
                      <div 
                        v-for="m in courseModules" 
                        :key="m.id" 
                        class="border-2 rounded-xl p-4 transition-all duration-200 cursor-pointer"
                        :class="selectedModule?.id === m.id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'"
                        @click="selectModule(m)"
                      >
                        <div class="flex items-center justify-between">
                          <div class="flex-1">
                            <div class="flex items-center mb-1">
                              <span class="inline-flex items-center justify-center w-6 h-6 bg-indigo-100 text-indigo-600 text-xs font-semibold rounded-full mr-3">
                                {{ m.sort_index ?? 0 }}
                              </span>
                              <h6 class="font-semibold text-gray-900">{{ m.title }}</h6>
                            </div>
                            <p v-if="m.description" class="text-sm text-gray-600 ml-9">{{ m.description }}</p>
                            <div class="flex items-center mt-2 ml-9 text-xs text-gray-500">
                              <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                              </svg>
                              {{ (m.lessons || []).length }} aula{{ (m.lessons || []).length !== 1 ? 's' : '' }}
                            </div>
                          </div>
                          <div class="flex items-center space-x-2">
                            <button 
                              class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors"
                              @click.stop="deleteModule(m)"
                            >
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                              </svg>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Coluna Direita - Aulas -->
                <div class="space-y-6">
                  <div class="bg-white border-2 border-gray-100 rounded-2xl p-6">
                    <div class="flex items-center mb-6">
                      <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                      </div>
                      <h5 class="text-lg font-semibold text-gray-900">
                        {{ selectedModule ? `Aulas - ${selectedModule.title}` : 'Aulas do Módulo' }}
                      </h5>
                    </div>

                    <div v-if="selectedModule">
                      <!-- Formulário de Nova Aula -->
                      <div class="space-y-4 mb-6">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                          <input 
                            v-model="lessonForm.title" 
                            class="col-span-2 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-0 transition-colors" 
                            placeholder="Título da aula"
                          >
                          <input 
                            v-model.number="lessonForm.sort_index" 
                            class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-0 transition-colors" 
                            type="number" 
                            min="0" 
                            placeholder="Ordem"
                          >
                        </div>
                        <input 
                          v-model="lessonForm.video_url" 
                          class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-0 transition-colors" 
                          placeholder="URL do vídeo (YouTube, HLS, MP4)"
                        >
                        <input 
                          v-model.number="lessonForm.duration_seconds" 
                          class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-0 transition-colors" 
                          type="number" 
                          min="0" 
                          placeholder="Duração em segundos (opcional)"
                        >
                        <button 
                          class="w-full bg-purple-600 text-white px-4 py-3 rounded-xl font-medium hover:bg-purple-700 transition-colors flex items-center justify-center" 
                          @click="createLesson"
                        >
                          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                          </svg>
                          Adicionar Aula
                        </button>
                      </div>

                      <!-- Lista de Aulas -->
                      <div class="space-y-3">
                        <div v-if="(selectedModule.lessons || []).length === 0" class="text-center py-8 text-gray-500">
                          <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                          </svg>
                          <p class="text-sm">Nenhuma aula criada ainda</p>
                          <p class="text-xs">Adicione aulas para este módulo</p>
                        </div>
                        
                        <div 
                          v-for="l in selectedModule.lessons || []" 
                          :key="l.id" 
                          class="border-2 border-gray-200 rounded-xl p-4 hover:border-gray-300 transition-colors"
                        >
                          <div class="flex items-center justify-between">
                            <div class="flex-1">
                              <div class="flex items-center mb-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-100 text-purple-600 text-xs font-semibold rounded-full mr-3">
                                  {{ l.sort_index ?? 0 }}
                                </span>
                                <h6 class="font-semibold text-gray-900">{{ l.title }}</h6>
                              </div>
                              <div class="ml-9 space-y-1">
                                <div class="flex items-center text-xs text-gray-500">
                                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                  </svg>
                                  {{ l.video_url || 'Sem vídeo' }}
                                </div>
                                <div v-if="l.duration_seconds" class="flex items-center text-xs text-gray-500">
                                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                  </svg>
                                  {{ Math.floor(l.duration_seconds / 60) }}:{{ String(l.duration_seconds % 60).padStart(2, '0') }}
                                </div>
                              </div>
                            </div>
                            <div class="flex items-center space-x-2">
                              <button 
                                class="text-blue-500 hover:text-blue-700 p-2 rounded-lg hover:bg-blue-50 transition-colors"
                                @click="previewLesson(l)" 
                                :disabled="!l.video_url"
                                :class="{ 'opacity-50 cursor-not-allowed': !l.video_url }"
                              >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m-6-8h1m4 0h1M9 6h6"></path>
                                </svg>
                              </button>
                              <button 
                                class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors"
                                @click="deleteLesson(selectedModule, l)"
                              >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div v-else class="text-center py-12 text-gray-500">
                      <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                      </svg>
                      <p class="text-lg font-medium mb-2">Selecione um Módulo</p>
                      <p class="text-sm">Escolha um módulo à esquerda para gerenciar suas aulas</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Lesson preview player -->
            <div v-if="selectedLesson && selectedLesson.video_url" class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Pré-visualização da Aula: {{ selectedLesson.title }}</label>
              <iframe
                v-if="isYoutubePreview && youtubeEmbedUrl"
                :src="youtubeEmbedUrl"
                class="w-full aspect-video rounded border"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
              ></iframe>
              <video v-else ref="lessonVideoEl" class="video-js vjs-default-skin vjs-16-9 w-full rounded border" playsinline controls preload="metadata"></video>
            </div>
            <!-- Botões de Navegação Step 4 -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
              <button type="button" 
                      class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200" 
                      @click="wizardStep = 3">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Anterior
              </button>
              
              <div class="flex space-x-3">
                <button type="button" 
                        class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200" 
                        @click="closeWizard">
                  Cancelar
                </button>
                <button type="button" 
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-sm text-sm font-medium rounded-xl hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200" 
                        @click="wizardStep = 5">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                  Continuar
                </button>
              </div>
            </div>
          </div>

          <!-- Step 5: Publicação/Resumo -->
          <div v-if="wizardStep === 5" class="space-y-8">
            <div class="text-center">
              <div class="mx-auto w-16 h-16 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <h4 class="text-xl font-semibold text-gray-900 mb-2">Revisão Final</h4>
              <p class="text-gray-600">Confira todas as informações antes de publicar o curso</p>
            </div>

            <div class="max-w-4xl mx-auto">
              <div class="bg-white border-2 border-gray-100 rounded-2xl p-8 space-y-8">
                <!-- Informações Básicas -->
                <div>
                  <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Informações Básicas
                  </h5>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                      <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 w-20 mt-1">Título:</span>
                        <span class="text-sm text-gray-900 font-medium flex-1">{{ newCurso.titulo || 'Não definido' }}</span>
                      </div>
                      <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 w-20 mt-1">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :class="{
                                'bg-yellow-100 text-yellow-800': newCurso.status === 'rascunho',
                                'bg-green-100 text-green-800': newCurso.status === 'publicado',
                                'bg-gray-100 text-gray-800': newCurso.status === 'arquivado'
                              }">
                          {{ displayStatus(newCurso.status) }}
                        </span>
                      </div>
                      <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 w-20 mt-1">Categoria:</span>
                        <span class="text-sm text-gray-900 font-medium flex-1">{{ newCurso.categoria || 'Não definida' }}</span>
                      </div>
                    </div>
                    <div class="space-y-4">
                      <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 w-20 mt-1">Instrutor:</span>
                        <span class="text-sm text-gray-900 font-medium flex-1">{{ newCurso.instrutor || 'Não definido' }}</span>
                      </div>
                      <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 w-20 mt-1">Módulos:</span>
                        <span class="text-sm text-gray-900 font-medium flex-1">{{ courseModules.length }} módulo{{ courseModules.length !== 1 ? 's' : '' }}</span>
                      </div>
                      <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 w-20 mt-1">Aulas:</span>
                        <span class="text-sm text-gray-900 font-medium flex-1">{{ courseModules.reduce((total, m) => total + (m.lessons?.length || 0), 0) }} aula{{ courseModules.reduce((total, m) => total + (m.lessons?.length || 0), 0) !== 1 ? 's' : '' }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Descrição -->
                <div v-if="newCurso.descricao">
                  <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                    </svg>
                    Descrição
                  </h5>
                  <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-sm text-gray-700 leading-relaxed">{{ newCurso.descricao }}</p>
                  </div>
                </div>

                <!-- Thumbnail -->
                <div v-if="thumbPreviewUrl">
                  <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Imagem do Curso
                  </h5>
                  <div class="flex justify-center">
                    <img :src="thumbPreviewUrl" alt="Thumbnail do curso" class="h-32 w-56 object-cover rounded-xl shadow-lg border-2 border-gray-200">
                  </div>
                </div>

                <!-- Estrutura do Curso -->
                <div v-if="courseModules.length > 0">
                  <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Estrutura do Curso
                  </h5>
                  <div class="space-y-3">
                    <div v-for="(module, index) in courseModules" :key="module.id" class="border border-gray-200 rounded-xl p-4">
                      <div class="flex items-center mb-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 bg-emerald-100 text-emerald-600 text-xs font-semibold rounded-full mr-3">
                          {{ index + 1 }}
                        </span>
                        <h6 class="font-semibold text-gray-900">{{ module.title }}</h6>
                        <span class="ml-auto text-xs text-gray-500">{{ (module.lessons || []).length }} aula{{ (module.lessons || []).length !== 1 ? 's' : '' }}</span>
                      </div>
                      <div v-if="(module.lessons || []).length > 0" class="ml-9 space-y-1">
                        <div v-for="(lesson, lessonIndex) in module.lessons" :key="lesson.id" class="flex items-center text-sm text-gray-600">
                          <span class="w-4 h-4 bg-gray-100 rounded-full flex items-center justify-center text-xs mr-2">{{ lessonIndex + 1 }}</span>
                          {{ lesson.title }}
                          <span v-if="lesson.duration_seconds" class="ml-auto text-xs text-gray-400">
                            {{ Math.floor(lesson.duration_seconds / 60) }}:{{ String(lesson.duration_seconds % 60).padStart(2, '0') }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Alertas de Validação -->
                <div class="space-y-3">
                  <div v-if="!newCurso.titulo" class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-center">
                      <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      <div>
                        <p class="text-sm font-medium text-red-800">Título obrigatório</p>
                        <p class="text-xs text-red-600">O curso precisa ter um título definido</p>
                      </div>
                    </div>
                  </div>
                  
                  <div v-if="courseModules.length === 0" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <div class="flex items-center">
                      <svg class="w-5 h-5 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                      </svg>
                      <div>
                        <p class="text-sm font-medium text-yellow-800">Nenhum módulo criado</p>
                        <p class="text-xs text-yellow-600">Recomendamos adicionar pelo menos um módulo com aulas</p>
                      </div>
                    </div>
                  </div>
                  
                  <div v-if="courseModules.length > 0 && courseModules.reduce((total, m) => total + (m.lessons?.length || 0), 0) === 0" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <div class="flex items-center">
                      <svg class="w-5 h-5 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                      </svg>
                      <div>
                        <p class="text-sm font-medium text-yellow-800">Nenhuma aula criada</p>
                        <p class="text-xs text-yellow-600">Adicione aulas aos módulos para completar o curso</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
              <button type="button" 
                      class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200" 
                      @click="wizardStep = 4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Anterior
              </button>
              
              <div class="flex space-x-3">
                <button type="button" 
                        class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200" 
                        @click="closeWizard">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                  Cancelar
                </button>
                
                <button type="button" 
                        class="inline-flex items-center px-8 py-3 border border-transparent text-sm font-medium rounded-xl text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200"
                        :class="{
                          'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700': newCurso.titulo,
                          'bg-gray-400 cursor-not-allowed': !newCurso.titulo
                        }"
                        @click="publishCourse" 
                        :disabled="!newCurso.titulo">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                  </svg>
                  {{ newCurso.titulo ? 'Adicionar Aulas' : 'Título Obrigatório' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { adminCursosApi } from '../../composables/adminCursosApi'
import axios from 'axios'
// Video.js v8: usar a entrada ESM moderna "core"
import videojs from 'video.js'
import 'video.js/dist/video-js.css'
export default {
  name: 'AdminCursos',
  data() {
    return {
      searchTerm: '',
      filterCategory: '',

      showCreateModal: false,
      isEditing: false,
      editingId: null,
      loading: false,
      error: null,
      categories: [],
      // Pagination & sorting
      page: 1,
      perPage: 10,
      total: 0,
      sortBy: 'created_at',
      sortDir: 'desc',
      newCurso: {
        titulo: '',
        descricao: '',
        status: 'rascunho',
        categoria: '',
        instrutor: '',
        duracao: '',
        videoKey: null,
        thumbnailKey: null,
        captions: []
      },
      // Video upload state
      videoFile: null,
      uploading: false,
      uploadProgress: 0,
      videoError: null,
      // Thumbnail upload state
      thumbFile: null,
      thumbUploading: false,
      thumbProgress: 0,
      thumbError: null,
      thumbPreviewUrl: null,
      // Captions upload state
      captionFiles: [],
      captionsUploading: false,
      captionsProgress: 0,
      captionsError: null,
      // Playback state
      playback: { hlsUrl: null, mp4Url: null, captions: [] },
      lastProgressSentAt: 0,
      hlsInstance: null,
      player: null,
      lessonPlayer: null,
      selectedLesson: null,
      isYoutubePreview: false,
      youtubeEmbedUrl: null,
      // Upload validation config
      maxUploadSizeBytes: 1024 * 1024 * 1024 * 2, // 2 GB
      allowedVideoTypes: ['video/mp4', 'video/quicktime', 'video/webm', 'video/x-matroska'],
      maxImageSizeBytes: 5 * 1024 * 1024, // 5 MB
      allowedImageTypes: ['image/jpeg', 'image/png', 'image/webp'],
      allowedCaptionTypes: ['text/vtt', 'application/x-subrip'],
      cursos: [],
      // Wizard state
      wizardStep: 1,
      courseModules: [],
      selectedModule: null,
      moduleForm: { title: '', description: '', sort_index: 0 },
      lessonForm: { title: '', video_url: '', duration_seconds: 0, sort_index: 0 }
    }
  },
  mounted() {
    this.loadCursos()
    this.loadCategories()
  },
  computed: {
    filteredCursos() {
      return this.cursos.filter(curso => {
        const title = (curso.titulo || curso.title || '').toString()
        const instructor = (curso.instrutor || '').toString()
        const haystack = (title + ' ' + instructor).toLowerCase()
        const matchesSearch = !this.searchTerm || haystack.includes(this.searchTerm.toLowerCase())
        const matchesCategory = !this.filterCategory || (curso.categoria === this.filterCategory)
        return matchesSearch && matchesCategory
      })
    }
  },
  methods: {
    mapStatusToEN(status) {
      const s = (status || '').toString().toLowerCase()
      if (s === 'publicado' || s === 'published') return 'published'
      if (s === 'rascunho' || s === 'draft') return 'draft'
      if (s === 'arquivado' || s === 'archived') return 'archived'
      return status
    },
    displayStatus(status) {
      const s = (status || '').toString().toLowerCase()
      if (s === 'published' || s === 'publicado') return 'Publicado'
      if (s === 'draft' || s === 'rascunho') return 'Rascunho'
      if (s === 'archived' || s === 'arquivado') return 'Arquivado'
      return status || '-'
    },
    getStatusClass(status) {
      const s = (status || '').toString().toLowerCase()
      if (s === 'published' || s === 'publicado') return 'bg-green-100 text-green-800'
      if (s === 'draft' || s === 'rascunho') return 'bg-gray-100 text-gray-800'
      if (s === 'archived' || s === 'arquivado') return 'bg-yellow-100 text-yellow-800'
      return 'bg-gray-100 text-gray-800'
    },
    openWizardNew() {
      this.isEditing = false
      this.editingId = null
      this.resetForm()
      this.wizardStep = 1
      this.showCreateModal = true
    },
    closeWizard() {
      this.showCreateModal = false
      this.resetForm()
    },
    async loadCategories() {
      try {
        const resp = await adminCursosApi.listCategories()
        if (Array.isArray(resp)) {
          this.categories = resp
        } else if (Array.isArray(resp?.items)) {
          this.categories = resp.items
        } else if (Array.isArray(resp?.data)) {
          this.categories = resp.data
        } else {
          this.categories = []
        }
      } catch (_) {
        // Silencioso por enquanto; podemos mostrar aviso se necessário
        this.categories = []
      }
    },
    async loadCursos(params = {}) {
      this.loading = true
      this.error = null
      try {
        const query = {
          page: this.page,
          per_page: this.perPage,
          sortBy: this.sortBy,
          sortDir: this.sortDir,
          search: this.searchTerm || undefined,
          category: this.filterCategory || undefined,
          ...params
        }
        const resp = await adminCursosApi.list(query)
        // Tenta diferentes formatos de resposta: array direto, {items}, {data}
        if (Array.isArray(resp)) {
          this.cursos = resp
          this.total = resp.length
        } else if (Array.isArray(resp?.items)) {
          this.cursos = resp.items
          this.total = resp?.total ?? resp?.meta?.total ?? this.cursos.length
        } else if (Array.isArray(resp?.data)) {
          this.cursos = resp.data
          this.total = resp?.total ?? resp?.meta?.total ?? this.cursos.length
        } else {
          this.cursos = []
          this.total = 0
        }
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Erro ao carregar cursos'
      } finally {
        this.loading = false
      }
    },
    changePage(newPage) {
      if (newPage < 1) return
      const maxPage = Math.max(1, Math.ceil(this.total / this.perPage))
      this.page = Math.min(newPage, maxPage)
      this.loadCursos()
    },
    changeSort(field) {
      if (this.sortBy === field) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'
      } else {
        this.sortBy = field
        this.sortDir = 'asc'
      }
      this.loadCursos()
    },
    resetForm() {
      this.newCurso = { titulo: '', descricao: '', categoria: '', instrutor: '', duracao: '', videoKey: null, thumbnailKey: null, captions: [] }
      this.videoFile = null
      this.uploading = false
      this.uploadProgress = 0
      this.videoError = null
      this.thumbFile = null
      this.thumbUploading = false
      this.thumbProgress = 0
      this.thumbError = null
      if (this.thumbPreviewUrl) { try { URL.revokeObjectURL(this.thumbPreviewUrl) } catch (_) {} }
      this.thumbPreviewUrl = null
      this.captionFiles = []
      this.captionsUploading = false
      this.captionsProgress = 0
      this.captionsError = null
      this.isEditing = false
      this.editingId = null
      this.playback = { hlsUrl: null, mp4Url: null, captions: [] }
      this.lastProgressSentAt = 0
      if (this.hlsInstance) {
        try { this.hlsInstance.destroy() } catch (_) {}
        this.hlsInstance = null
      }
      if (this.player) {
        try { this.player.dispose() } catch (_) {}
        this.player = null
      }
      this.wizardStep = 1
      this.courseModules = []
      this.selectedModule = null
      this.selectedLesson = null
      this.moduleForm = { title: '', description: '', sort_index: 0 }
      this.lessonForm = { title: '', video_url: '', duration_seconds: 0, sort_index: 0 }
      if (this.lessonPlayer) {
        try { this.lessonPlayer.dispose() } catch (_) {}
        this.lessonPlayer = null
      }
    },
    async saveStep1() {
      // Create the base course and move to step 2
      try {
        if (!this.newCurso.titulo) return
        this.loading = true
        this.error = null
        const payload = {
          title: this.newCurso.titulo,
          description: this.newCurso.descricao || '',
          status: this.mapStatusToEN(this.newCurso.status || 'draft'),
          sort_index: 0
        }
        const resp = await adminCursosApi.create(payload)
        const course = resp?.data || resp
        this.isEditing = true
        this.editingId = course?.id
        this.wizardStep = 2
        return course
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Erro ao criar curso'
      } finally {
        this.loading = false
      }
    },
    async saveStep2() {
      if (!this.editingId) return
      try {
        this.loading = true
        this.error = null
        const payload = {
          title: this.newCurso.titulo,
          description: this.newCurso.descricao || '',
          status: this.mapStatusToEN(this.newCurso.status || 'draft')
        }
        await adminCursosApi.update(this.editingId, payload)
        this.wizardStep = 3
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Erro ao guardar informações'
      } finally {
        this.loading = false
      }
    },
    async saveStep3() {
      if (!this.editingId) return
      try {
        this.loading = true
        this.error = null
        if (this.thumbFile) {
          const fd = new FormData()
          fd.append('thumbnail', this.thumbFile)
          await adminCursosApi.update(this.editingId, fd)
        }
        this.wizardStep = 4
        // Carregar módulos existentes do curso
        await this.fetchCourseDetail()
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Erro ao guardar mídia'
      } finally {
        this.loading = false
      }
    },
    async publishCourse() {
      if (!this.editingId) return
      try {
        this.loading = true
        this.error = null
        
        // Salva o curso como rascunho primeiro
        const payload = {
          title: this.newCurso.titulo,
          description: this.newCurso.descricao || '',
          status: this.mapStatusToEN(this.newCurso.status || 'draft')
        }
        await adminCursosApi.update(this.editingId, payload)
        
        // Atualiza tabela principal
        await this.loadCursos()
        
        // Fecha wizard
        this.closeWizard()
        
        // Redireciona para a página de adição de aulas do curso
        this.$router.push(`/admin/cursos/${this.editingId}/aulas`)
        
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Erro ao salvar curso'
      } finally {
        this.loading = false
      }
    },
    async finishWizard() {
      try {
        await this.loadCursos()
        this.closeWizard()
      } catch (_) {
        this.closeWizard()
      }
    },
    async fetchCourseDetail() {
      if (!this.editingId) return
      try {
        const resp = await adminCursosApi.get(this.editingId)
        const course = resp?.data || resp
        this.courseModules = Array.isArray(course?.modules) ? course.modules : []
        // Ensure each module has lessons array
        this.courseModules = this.courseModules.map(m => ({ ...m, lessons: Array.isArray(m?.lessons) ? m.lessons : [] }))
      } catch (_) {
        this.courseModules = []
      }
    },
    previewLesson(l) {
      this.selectedLesson = l
      const src = l?.video_url || ''
      // If YouTube -> use iframe and skip video.js
      if (this.isYouTubeUrl(src)) {
        this.isYoutubePreview = true
        this.youtubeEmbedUrl = this.toYouTubeEmbed(src)
        if (this.lessonPlayer) {
          try { this.lessonPlayer.dispose() } catch (_) {}
          this.lessonPlayer = null
        }
        return
      }
      // Otherwise use video.js
      this.isYoutubePreview = false
      this.youtubeEmbedUrl = null
      this.$nextTick(() => {
        const el = this.$refs.lessonVideoEl
        if (!el) return
        if (this.lessonPlayer) {
          try { this.lessonPlayer.dispose() } catch (_) {}
          this.lessonPlayer = null
        }
        const type = typeof src === 'string' && src.endsWith('.m3u8') ? 'application/x-mpegURL' : 'video/mp4'
        this.lessonPlayer = videojs(el, {
          sources: [{ src, type }],
          controls: true,
          preload: 'metadata',
          fluid: true,
          playbackRates: [0.5, 1, 1.25, 1.5, 1.75, 2]
        })
      })
    },
    isYouTubeUrl(url) {
      if (!url || typeof url !== 'string') return false
      return /^(https?:)?\/\/(www\.)?(youtube\.com|youtu\.be)\//i.test(url)
    },
    toYouTubeEmbed(url) {
      const base = import.meta.env.VITE_YOUTUBE_EMBED_BASE || 'https://www.youtube.com/embed/'
      // Try to extract the video ID from common patterns
      const idMatch = (
        url.match(/[?&]v=([\w-]{11})/) || // watch?v=
        url.match(/youtu\.be\/([\w-]{11})/) ||
        url.match(/embed\/([\w-]{11})/) ||
        []
      )
      const id = idMatch && idMatch[1] ? idMatch[1] : ''
      if (!id) return null
      return `${base}${id}`
    },
    async createModule() {
      if (!this.editingId || !this.moduleForm.title) return
      try {
        const payload = { title: this.moduleForm.title, description: this.moduleForm.description || '', sort_index: this.moduleForm.sort_index || 0 }
        const resp = await adminCursosApi.addModule(this.editingId, payload)
        const mod = resp?.data || resp
        this.courseModules.push({ ...mod, lessons: [] })
        this.moduleForm = { title: '', description: '', sort_index: 0 }
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Erro ao criar módulo'
      }
    },
    selectModule(m) {
      this.selectedModule = m
    },
    async deleteModule(m) {
      if (!this.editingId || !m?.id) return
      if (!confirm('Remover este módulo?')) return
      try {
        await adminCursosApi.deleteModule(this.editingId, m.id)
        this.courseModules = this.courseModules.filter(x => x.id !== m.id)
        if (this.selectedModule?.id === m.id) this.selectedModule = null
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Erro ao remover módulo'
      }
    },
    async createLesson() {
      if (!this.editingId || !this.selectedModule?.id || !this.lessonForm.title) return
      try {
        const payload = {
          title: this.lessonForm.title,
          video_url: this.lessonForm.video_url || '',
          duration_seconds: this.lessonForm.duration_seconds || 0,
          sort_index: this.lessonForm.sort_index || 0
        }
        const resp = await adminCursosApi.addLesson(this.editingId, this.selectedModule.id, payload)
        const lesson = resp?.data || resp
        if (!Array.isArray(this.selectedModule.lessons)) this.selectedModule.lessons = []
        this.selectedModule.lessons.push(lesson)
        // atualizar referência também no array principal
        this.courseModules = this.courseModules.map(m => m.id === this.selectedModule.id ? { ...this.selectedModule } : m)
        this.lessonForm = { title: '', video_url: '', duration_seconds: 0, sort_index: 0 }
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Erro ao criar aula'
      }
    },
    async deleteLesson(mod, l) {
      if (!this.editingId || !mod?.id || !l?.id) return
      if (!confirm('Remover esta aula?')) return
      try {
        await adminCursosApi.deleteLesson(this.editingId, mod.id, l.id)
        mod.lessons = (mod.lessons || []).filter(x => x.id !== l.id)
      } catch (e) {
        this.error = e?.response?.data?.message || e?.message || 'Erro ao remover aula'
      }
    },
    async editCurso(curso) {
      this.isEditing = true
      this.editingId = curso.id
      this.newCurso = {
        titulo: curso.titulo || curso.title || '',
        descricao: curso.descricao || curso.description || '',
        status: curso.status || 'rascunho',
        categoria: curso.categoria || '',
        instrutor: curso.instrutor || '',
        duracao: curso.duracao || '',
        videoKey: curso.videoKey || null,
        thumbnailKey: curso.thumbnailKey || null,
        captions: Array.isArray(curso.captions) ? curso.captions : []
      }
      this.videoFile = null
      this.uploadProgress = 0
      this.videoError = null
      this.showCreateModal = true
      this.wizardStep = 2
      await this.fetchCourseDetail()
    },
    deleteCurso(id) {
      if (!confirm('Tem a certeza que deseja eliminar este curso?')) return
      this.loading = true
      this.error = null
      adminCursosApi.remove(id)
        .then(() => this.loadCursos())
        .catch((e) => {
          this.error = e?.response?.data?.message || e?.message || 'Erro ao eliminar curso'
        })
        .finally(() => { this.loading = false })
    },
    onPickVideo() {
      this.$refs.videoInput?.click()
    },
    async onVideoSelected(e) {
      const file = e?.target?.files?.[0]
      if (!file) return
      // Upload validations
      if (file.size > this.maxUploadSizeBytes) {
        this.videoError = `Ficheiro excede o tamanho máximo permitido (${(this.maxUploadSizeBytes / (1024*1024)).toFixed(0)} MB)`
        return
      }
      if (this.allowedVideoTypes.length && !this.allowedVideoTypes.includes(file.type)) {
        this.videoError = 'Tipo de ficheiro não permitido. Utilize MP4, MOV, WEBM ou MKV.'
        return
      }
      this.videoFile = file
      this.videoError = null
      try {
        this.uploading = true
        this.uploadProgress = 0
        // init upload to get presigned URL or session
        const initResp = await adminCursosApi.initUpload(this.editingId || 'new', {
          filename: file.name,
          contentType: file.type || 'application/octet-stream',
          size: file.size || 0
        })
        const uploadUrl = initResp?.uploadUrl
        const uploadId = initResp?.uploadId
        const key = initResp?.key || initResp?.objectKey
        if (!uploadUrl) {
          throw new Error('Upload URL não recebido do servidor')
        }
        await axios.put(uploadUrl, file, {
          headers: { 'Content-Type': file.type || 'application/octet-stream' },
          onUploadProgress: (evt) => {
            if (evt.total) this.uploadProgress = Math.round((evt.loaded / evt.total) * 100)
          }
        })
        // notify backend upload complete if required
        try {
          if (uploadId) {
            await adminCursosApi.completeUpload(this.editingId || 'new', { uploadId, parts: [] })
          }
        } catch (_) { /* noop optional */ }

        // store reference for create/update payload
        this.newCurso.videoKey = key || file.name

        // If editing existing curso, fetch playback URLs and show preview
        if (this.editingId) {
          await this.loadPlayback(this.editingId)
        }
      } catch (err) {
        this.videoError = err?.response?.data?.message || err?.message || 'Falha no upload do vídeo'
      } finally {
        this.uploading = false
      }
    },
    onPickThumb() {
      this.$refs.thumbInput?.click()
    },
    async onThumbSelected(e) {
      const file = e?.target?.files?.[0]
      if (!file) return
      if (file.size > this.maxImageSizeBytes) {
        this.thumbError = `Imagem excede o tamanho máximo permitido (${(this.maxImageSizeBytes / (1024*1024)).toFixed(0)} MB)`
        return
      }
      if (this.allowedImageTypes.length && !this.allowedImageTypes.includes(file.type)) {
        this.thumbError = 'Tipo de imagem não permitido. Utilize JPEG, PNG ou WEBP.'
        return
      }
      this.thumbFile = file
      this.thumbError = null
      this.thumbUploading = true
      this.thumbProgress = 0
      try {
        const initResp = await adminCursosApi.initUpload(this.editingId || 'new', {
          filename: file.name,
          contentType: file.type || 'application/octet-stream',
          size: file.size || 0
        })
        const uploadUrl = initResp?.uploadUrl
        const uploadId = initResp?.uploadId
        const key = initResp?.key || initResp?.objectKey
        if (!uploadUrl) throw new Error('Upload URL de imagem não recebido do servidor')
        await axios.put(uploadUrl, file, {
          headers: { 'Content-Type': file.type || 'application/octet-stream' },
          onUploadProgress: (evt) => {
            if (evt.total) this.thumbProgress = Math.round((evt.loaded / evt.total) * 100)
          }
        })
        try { if (uploadId) await adminCursosApi.completeUpload(this.editingId || 'new', { uploadId, parts: [] }) } catch (_) {}
        this.newCurso.thumbnailKey = key || file.name
        if (this.thumbPreviewUrl) { try { URL.revokeObjectURL(this.thumbPreviewUrl) } catch (_) {} }
        this.thumbPreviewUrl = URL.createObjectURL(file)
      } catch (err) {
        this.thumbError = err?.response?.data?.message || err?.message || 'Falha no upload da thumbnail'
      } finally {
        this.thumbUploading = false
      }
    },
    onPickCaptions() {
      this.$refs.captionsInput?.click()
    },
    async onCaptionsSelected(e) {
      const files = Array.from(e?.target?.files || [])
      if (!files.length) return
      for (const f of files) {
        const typeOk = this.allowedCaptionTypes.includes(f.type) || /\.(vtt|srt)$/i.test(f.name)
        if (!typeOk) {
          this.captionsError = 'Legenda inválida. Utilize .vtt ou .srt.'
          return
        }
      }
      this.captionsError = null
      this.captionsUploading = true
      this.captionsProgress = 0
      try {
        let done = 0
        for (const file of files) {
          const initResp = await adminCursosApi.initUpload(this.editingId || 'new', {
            filename: file.name,
            contentType: file.type || 'text/vtt',
            size: file.size
          })
          const uploadUrl = initResp?.uploadUrl
          const uploadId = initResp?.uploadId
          const key = initResp?.key || initResp?.objectKey
          if (!uploadUrl) throw new Error('Upload URL de legenda não recebido')
          await axios.put(uploadUrl, file, {
            headers: { 'Content-Type': file.type || 'text/vtt' },
            onUploadProgress: (evt) => {
              if (evt.total) {
                const pct = Math.round((evt.loaded / evt.total) * 100)
                this.captionsProgress = Math.round((done + pct/100) / files.length * 100)
              }
            }
          })
          try { if (uploadId) await adminCursosApi.completeUpload(this.editingId || 'new', { uploadId, parts: [] }) } catch (_) {}
          // derive lang/label from filename e.g. captions.en.vtt
          const match = file.name.match(/\.(en|pt|es|fr|de)\.(vtt|srt)$/i)
          const lang = match ? match[1] : 'pt'
          const label = lang.toUpperCase()
          this.newCurso.captions.push({ key: key || file.name, lang, label })
          done += 1
          this.captionsProgress = Math.round((done / files.length) * 100)
        }
      } catch (err) {
        this.captionsError = err?.response?.data?.message || err?.message || 'Falha no upload das legendas'
      } finally {
        this.captionsUploading = false
      }
    },
    removeCaption(idx) {
      this.newCurso.captions.splice(idx, 1)
    },
    onPreviewLoadedMetadata(e) {
      const el = e?.target
      if (!el || !isFinite(el.duration)) return
      const seconds = Math.floor(el.duration)
      this.newCurso.duracao = this.formatDuration(seconds)
    },
    formatDuration(totalSec) {
      const h = Math.floor(totalSec / 3600)
      const m = Math.floor((totalSec % 3600) / 60)
      const s = totalSec % 60
      if (h > 0) return `${h}h ${m}m`
      if (m > 0) return `${m}m ${s}s`
      return `${s}s`
    },
    async loadPlayback(courseId) {
      try {
        const resp = await adminCursosApi.getPlaybackUrls(courseId)
        // Normalize structure
        const data = resp?.data || resp
        this.playback = {
          hlsUrl: data?.hlsUrl || null,
          mp4Url: data?.mp4Url || null,
          captions: Array.isArray(data?.captions) ? data.captions : []
        }
        // Prepare Video.js player
        this.$nextTick(() => {
          const el = this.$refs.videoJsEl
          if (!el) return
          if (this.player) {
            try { this.player.dispose() } catch (_) {}
            this.player = null
          }
          const sources = []
          if (this.playback.hlsUrl) sources.push({ src: this.playback.hlsUrl, type: 'application/x-mpegURL' })
          if (this.playback.mp4Url) sources.push({ src: this.playback.mp4Url, type: 'video/mp4' })
          this.player = videojs(el, {
            sources,
            controls: true,
            preload: 'metadata',
            fluid: true,
            playbackRates: [0.5, 1, 1.25, 1.5, 1.75, 2]
          })
          // Add caption tracks if provided (expects objects with url/src, label, lang/srclang, default)
          const caps = Array.isArray(this.playback.captions) ? this.playback.captions : []
          caps.forEach((c) => {
            const track = {
              kind: 'subtitles',
              src: c.url || c.src,
              srclang: c.srclang || c.lang || 'pt',
              label: c.label || (c.lang ? c.lang.toUpperCase() : 'PT'),
              default: !!c.default
            }
            if (track.src) {
              this.player.addRemoteTextTrack(track, false)
            }
          })
          // Restore last position if provided
          if (typeof data?.positionSeconds === 'number' && data.positionSeconds > 0) {
            const restoreTime = Math.max(0, data.positionSeconds - 1)
            this.player.one('loadedmetadata', () => {
              try { this.player.currentTime(restoreTime) } catch (_) {}
            })
          }
          // Wire events
          this.player.on('timeupdate', () => {
            const current = this.player.currentTime() || 0
            const duration = this.player.duration() || 0
            const fakeEvt = { target: { currentTime: current, duration } }
            this.onVideoTimeUpdate(fakeEvt)
          })
          this.player.on('ended', () => this.onVideoEnded())
          this.player.on('loadedmetadata', () => {
            const d = Math.floor(this.player.duration() || 0)
            if (d > 0) this.newCurso.duracao = this.formatDuration(d)
          })
        })
      } catch (_) {
        this.playback = { hlsUrl: null, mp4Url: null, captions: [] }
      }
    },
    async onVideoTimeUpdate(evt) {
      if (!this.editingId) return
      const el = evt.target
      const current = el.currentTime || 0
      const duration = el.duration || 0
      if (!duration) return
      const percent = Math.min(100, Math.round((current / duration) * 100))
      const now = Date.now()
      if (now - this.lastProgressSentAt < 5000 && percent < 100) return
      try {
        await adminCursosApi.saveProgress(this.editingId, {
          positionSeconds: Math.floor(current),
          percent
        })
        this.lastProgressSentAt = now
      } catch (_) { /* swallow */ }
    },
    async onVideoEnded() {
      if (!this.editingId) return
      try {
        await adminCursosApi.saveProgress(this.editingId, { positionSeconds: 0, percent: 100, completed: true })
      } catch (_) { /* noop */ }
    }
  }
}
</script>