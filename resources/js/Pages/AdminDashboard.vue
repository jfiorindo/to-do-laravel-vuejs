<template>
  <div>
    <h1 class="text-3xl font-bold mb-6">Painel do Administrador</h1>

    <!-- Formulário de nova tarefa -->
    <form @submit.prevent="criarTarefa" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <input v-model="novaTarefa.title" type="text" placeholder="Título" class="border p-2 rounded" required>
      <input v-model="novaTarefa.description" type="text" placeholder="Descrição" class="border p-2 rounded" required>
      <input v-model="novaTarefa.due_date" type="date" class="border p-2 rounded" required>
      <select v-model="novaTarefa.user_id" class="border p-2 rounded" required>
        <option disabled value="">Atribuir a...</option>
        <option v-for="user in usuarios" :key="user.id" :value="user.id">
          {{ user.name }}
        </option>
      </select>
      <div class="md:col-span-4 text-right">
        <button type="submit" class="bg-green-600 text-white text-lg font-semibold px-8 py-3 rounded-lg shadow hover:bg-green-700 transition-all">
          ➕ Adicionar Tarefa
        </button>
      </div>
    </form>

    <!-- Lista de tarefas -->
    <div v-if="tarefas.length === 0">Nenhuma tarefa cadastrada.</div>

    <table class="min-w-full table-auto border">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 border">Título</th>
          <th class="px-4 py-2 border">Descrição</th>
          <th class="px-4 py-2 border">Vencimento</th>
          <th class="px-4 py-2 border">Usuário</th>
          <th class="px-4 py-2 border">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="tarefa in tarefas" :key="tarefa.id" class="text-center">
          <td class="border px-4 py-2">
            <div v-if="editandoTarefaId === tarefa.id">
              <input v-model="tarefa.title" class="border p-1 rounded">
            </div>
            <div v-else>{{ tarefa.title }}</div>
          </td>
          <td class="border px-4 py-2">
            <div v-if="editandoTarefaId === tarefa.id">
              <input v-model="tarefa.description" class="border p-1 rounded">
            </div>
            <div v-else>{{ tarefa.description }}</div>
          </td>
          <td class="border px-4 py-2">
            <div v-if="editandoTarefaId === tarefa.id">
              <input v-model="tarefa.due_date" type="date" class="border p-1 rounded">
            </div>
            <div v-else>{{ tarefa.due_date }}</div>
          </td>
          <td class="border px-4 py-2">{{ tarefa.user.name }}</td>
          <td class="border px-4 py-2">
            <div v-if="editandoTarefaId === tarefa.id">
              <button @click="atualizarTarefa(tarefa)" class="bg-blue-500 text-white px-2 py-1 rounded">Salvar</button>
              <button @click="cancelarEdicao" class="ml-2 px-2 py-1">Cancelar</button>
            </div>
            <div v-else>
              <button @click="editarTarefa(tarefa)" class="text-blue-600 mr-2">Editar</button>
              <button @click="excluirTarefa(tarefa.id)" class="text-red-600">Excluir</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
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
        const response = await axios.post('/api/tasks', this.novaTarefa)
        this.tarefas.push(response.data)
        this.novaTarefa = { title: '', description: '', due_date: '', user_id: '' }
        } catch (error) {
            console.error('Erro ao criar tarefa:', error)
            alert('Erro ao criar tarefa. Verifique os dados ou tente novamente.')
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
