import axios from '@/axios'


<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Painel do Administrador</h1>
      <div class="space-x-3">
        <button
          class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition"
          @click="$inertia.visit('/admin/users')"
        >
          Gerenciar Usuários
        </button>
        <button
          class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
          @click="logout"
        >
          Logout
        </button>
      </div>
    </div>

    <!-- Formulário de nova tarefa -->
    <form @submit.prevent="criarTarefa" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 bg-white p-6 rounded shadow">
      <input v-model="novaTarefa.title" type="text" placeholder="Título" class="border p-2 rounded w-full" required>
      <input v-model="novaTarefa.description" type="text" placeholder="Descrição" class="border p-2 rounded w-full" required>
      <input v-model="novaTarefa.due_date" type="date" class="border p-2 rounded w-full" required>
      <select v-model="novaTarefa.user_id" class="border p-2 rounded w-full" required>
        <option disabled value="">Atribuir a...</option>
        <option v-for="user in usuarios" :key="user.id" :value="user.id">
          {{ user.name }}
        </option>
      </select>
      <div class="md:col-span-4 text-right">
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
          ➕ Adicionar Tarefa
        </button>
      </div>
    </form>

    <!-- Botão para exibir/ocultar filtros -->
    <div class="mb-4">
      <button @click="mostrarFiltros = !mostrarFiltros" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
        {{ mostrarFiltros ? 'Esconder Filtros' : 'Mostrar Filtros' }}
      </button>
    </div>

    <!-- Filtros -->
    <div v-if="mostrarFiltros" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <input v-model="filtroTitulo" type="text" placeholder="Filtrar por título" class="border p-2 rounded">
      <input v-model="filtroUsuario" type="text" placeholder="Filtrar por nome do usuário" class="border p-2 rounded">
      <select v-model="filtroStatus" class="border p-2 rounded">
        <option value="">Todos os status</option>
        <option value="true">Concluída</option>
        <option value="false">Pendente</option>
      </select>
      <div class="flex flex-col">
        <label class="text-sm text-gray-600">Vencimento (de)</label>
        <input type="date" v-model="filtroVencimentoInicio" class="border p-2 rounded">
      </div>
      <div class="flex flex-col">
        <label class="text-sm text-gray-600">Vencimento (até)</label>
        <input type="date" v-model="filtroVencimentoFim" class="border p-2 rounded">
      </div>
    </div>

    <div v-if="tarefas.length === 0" class="text-center text-gray-500">Nenhuma tarefa cadastrada.</div>

    <div v-else class="overflow-x-auto">
      <table class="min-w-full bg-white shadow rounded">
        <thead class="bg-gray-200">
          <tr>
            <th class="text-left px-4 py-3 border-b">Título</th>
            <th class="text-left px-4 py-3 border-b">Descrição</th>
            <th class="text-left px-4 py-3 border-b">Vencimento</th>
            <th class="text-left px-4 py-3 border-b">Usuário</th>
            <th class="text-left px-4 py-3 border-b">Status</th>
            <th class="text-center px-4 py-3 border-b">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tarefa in tarefasFiltradasPaginadas" :key="tarefa.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 border-b">
              <div v-if="editandoTarefaId === tarefa.id">
                <input v-model="tarefaEditando.title" class="border p-1 rounded w-full">
              </div>
              <div v-else>{{ tarefa.title }}</div>
            </td>
            <td class="px-4 py-3 border-b">
              <div v-if="editandoTarefaId === tarefa.id">
                <input v-model="tarefaEditando.description" class="border p-1 rounded w-full">
              </div>
              <div v-else>{{ tarefa.description }}</div>
            </td>
            <td class="px-4 py-3 border-b">
              <div v-if="editandoTarefaId === tarefa.id">
                <input v-model="tarefaEditando.due_date" type="date" class="border p-1 rounded w-full">
              </div>
              <div v-else>{{ formatarData(tarefa.due_date) }}</div>
            </td>
            <td class="px-4 py-3 border-b">{{ tarefa.user.name }}</td>
            <td class="px-4 py-3 border-b">
              <span :class="tarefa.is_completed ? 'text-green-600' : 'text-red-600'">
                {{ tarefa.is_completed ? 'Concluída' : 'Pendente' }}
              </span>
            </td>
            <td class="px-4 py-3 border-b text-center">
              <div v-if="editandoTarefaId === tarefa.id">
                <button @click="atualizarTarefa(tarefa.id)" class="bg-blue-500 text-white px-3 py-1 rounded">Salvar</button>
                <button @click="cancelarEdicao" class="ml-2 px-3 py-1 border">Cancelar</button>
              </div>
              <div v-else>
                <button @click="editarTarefa(tarefa)" class="text-blue-600 hover:underline mr-3">Editar</button>
                <button @click="excluirTarefa(tarefa.id)" class="text-red-600 hover:underline">Excluir</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="mt-4 flex justify-center space-x-2">
        <button @click="paginaAtual--" :disabled="paginaAtual === 1" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50">
          Anterior
        </button>
        <span class="px-3 py-1">Página {{ paginaAtual }}</span>
        <button @click="paginaAtual++" :disabled="paginaAtual * porPagina >= tarefasFiltradas.length" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50">
          Próxima
        </button>
      </div>
    </div>
  </div>
  <div class="mb-6 text-center">
  <a
    href="/api/admin/export-tasks"
    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition"
  >
    Exportar Tarefas (CSV)
  </a>
</div>
</template>

<script>
import axios from 'axios'
import { router } from '@inertiajs/vue3'

export default {
  name: 'AdminDashboard',
  data() {
    return {
      tarefas: [],
      usuarios: [],
      novaTarefa: { title: '', description: '', due_date: '', user_id: '' },
      editandoTarefaId: null,
      tarefaEditando: {},
      paginaAtual: 1,
      porPagina: 10,
      filtroTitulo: '',
      filtroUsuario: '',
      filtroStatus: '',
      filtroVencimentoInicio: '',
      filtroVencimentoFim: '',
      mostrarFiltros: false
    }
  },
  computed: {
    tarefasFiltradas() {
      return this.tarefas.filter(tarefa => {
        const matchTitulo = this.filtroTitulo === '' || tarefa.title.toLowerCase().includes(this.filtroTitulo.toLowerCase())
        const matchUsuario = this.filtroUsuario === '' || tarefa.user.name.toLowerCase().includes(this.filtroUsuario.toLowerCase())
        const matchStatus = this.filtroStatus === '' || String(!!tarefa.is_completed) === this.filtroStatus
        const matchInicio = !this.filtroVencimentoInicio || new Date(tarefa.due_date) >= new Date(this.filtroVencimentoInicio)
        const matchFim = !this.filtroVencimentoFim || new Date(tarefa.due_date) <= new Date(this.filtroVencimentoFim)
        return matchTitulo && matchUsuario && matchStatus && matchInicio && matchFim
      })
    },
    tarefasFiltradasPaginadas() {
      const start = (this.paginaAtual - 1) * this.porPagina
      return this.tarefasFiltradas.slice(start, start + this.porPagina)
    }
  },
  methods: {
    logout() {
      router.post('/logout', {}, {
        onFinish: () => router.visit('/login')
      })
    },
    async carregarTarefas() {
      const response = await axios.get('/api/admin/tasks')
      this.tarefas = response.data
    },
    async carregarUsuarios() {
      const response = await axios.get('/api/admin/users')
      this.usuarios = response.data
    },
    async criarTarefa() {
      try {
        const hoje = new Date();
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() + 30);
        const dataInformada = new Date(this.novaTarefa.due_date);

        if (dataInformada < hoje) {
          alert('A data de vencimento não pode ser no passado.');
          return;
        }

        if (dataInformada > maxDate) {
          alert('A data de vencimento não pode ser mais de 30 anos no futuro.');
          return;
        }

        const duplicada = this.tarefas.find(tarefa =>
          tarefa.title.trim().toLowerCase() === this.novaTarefa.title.trim().toLowerCase() &&
          tarefa.user_id === this.novaTarefa.user_id
        );
        if (duplicada) {
          alert('Já existe uma tarefa com esse título para este usuário.');
          return;
        }

        await axios.post('/api/tasks', this.novaTarefa);
        this.novaTarefa = { title: '', description: '', due_date: '', user_id: '' };
        await this.carregarTarefas();
      } catch (error) {
        console.error('Erro ao criar tarefa:', error);
        alert('Erro ao criar tarefa. Verifique os dados ou tente novamente.');
      }
    },
    editarTarefa(tarefa) {
      this.editandoTarefaId = tarefa.id;
      this.tarefaEditando = { ...tarefa };
    },
    cancelarEdicao() {
      this.editandoTarefaId = null;
      this.tarefaEditando = {};
    },
    async atualizarTarefa(id) {
      await axios.put(`/api/tasks/${id}`, this.tarefaEditando);
      const index = this.tarefas.findIndex(t => t.id === id);
      if (index !== -1) {
        this.tarefas.splice(index, 1, { ...this.tarefaEditando });
      }
      this.cancelarEdicao();
    },
    async excluirTarefa(id) {
      await axios.delete(`/api/tasks/${id}`)
      this.tarefas = this.tarefas.filter(t => t.id !== id)
      await this.carregarTarefas();
    },
    formatarData(dataStr) {
      const [ano, mes, dia] = dataStr.split('-')
      return `${dia}/${mes}/${ano}`
    }
  },
  mounted() {
    this.carregarTarefas()
    this.carregarUsuarios()
  }
}

</script>

<style scoped>
table th, table td {
  text-align: left;
}
</style>
