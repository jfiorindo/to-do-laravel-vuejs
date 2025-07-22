<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Painel do Administrador</h1>
      <button
        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition"
        @click="$inertia.visit('/admin/users')"
      >
        Gerenciar Usuários
      </button>
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

    <!-- Lista de tarefas -->
    <div v-if="tarefas.length === 0" class="text-center text-gray-500">Nenhuma tarefa cadastrada.</div>

    <div v-else class="overflow-x-auto">
      <table class="min-w-full bg-white shadow rounded">
        <thead class="bg-gray-200">
          <tr>
            <th class="text-left px-4 py-3 border-b">Título</th>
            <th class="text-left px-4 py-3 border-b">Descrição</th>
            <th class="text-left px-4 py-3 border-b">Vencimento</th>
            <th class="text-left px-4 py-3 border-b">Usuário</th>
            <th class="text-center px-4 py-3 border-b">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tarefa in tarefas" :key="tarefa.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 border-b">
              <div v-if="editandoTarefaId === tarefa.id">
                <input v-model="tarefa.title" class="border p-1 rounded w-full">
              </div>
              <div v-else>{{ tarefa.title }}</div>
            </td>
            <td class="px-4 py-3 border-b">
              <div v-if="editandoTarefaId === tarefa.id">
                <input v-model="tarefa.description" class="border p-1 rounded w-full">
              </div>
              <div v-else>{{ tarefa.description }}</div>
            </td>
            <td class="px-4 py-3 border-b">
              <div v-if="editandoTarefaId === tarefa.id">
                <input v-model="tarefa.due_date" type="date" class="border p-1 rounded w-full">
              </div>
              <div v-else>{{ tarefa.due_date }}</div>
            </td>
            <td class="px-4 py-3 border-b">{{ tarefa.user.name }}</td>
            <td class="px-4 py-3 border-b text-center">
              <div v-if="editandoTarefaId === tarefa.id">
                <button @click="atualizarTarefa(tarefa)" class="bg-blue-500 text-white px-3 py-1 rounded">Salvar</button>
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
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'AdminDashboard',
  data() {
    return {
      tarefas: [],
      usuarios: [],
      novaTarefa: {
        title: '',
        description: '',
        due_date: '',
        user_id: ''
      },
      editandoTarefaId: null
    }
  },
  methods: {
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
      this.editandoTarefaId = tarefa.id
    },
    cancelarEdicao() {
      this.editandoTarefaId = null
    },
    async atualizarTarefa(tarefa) {
      await axios.put(`/api/tasks/${tarefa.id}`, tarefa)
      this.editandoTarefaId = null
    },
    async excluirTarefa(id) {
      await axios.delete(`/api/tasks/${id}`)
      this.tarefas = this.tarefas.filter(t => t.id !== id)
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
