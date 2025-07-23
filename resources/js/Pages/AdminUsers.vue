<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">👥 Gerenciar Usuários</h1>
    <div class="mb-4 flex justify-between items-center">
      <button
        @click="$inertia.visit('/admin')"
        class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition">
        ← Voltar para o Painel
      </button>
      <input
        v-model="filtroNome"
        type="text"
        placeholder="Buscar por nome"
        class="border px-3 py-2 rounded w-1/3"
      />
    </div>

    <!-- Botão de criar novo -->
    <div class="mb-4 text-right">
      <button @click="abrirModalCriar" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        ➕ Criar Novo Usuário
      </button>
    </div>

    <!-- Lista de usuários -->
    <table class="w-full bg-white shadow rounded" v-if="usuariosPaginados.length">
      <thead class="bg-gray-200">
        <tr>
          <th class="text-left px-4 py-3 border-b">Nome</th>
          <th class="text-left px-4 py-3 border-b">E-mail</th>
          <th class="text-left px-4 py-3 border-b">Admin</th>
          <th class="text-left px-4 py-3 border-b">Criado em</th>
          <th class="text-center px-4 py-3 border-b">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in usuariosPaginados" :key="user.id" class="hover:bg-gray-50">
          <td class="px-4 py-3 border-b">{{ user.name }}</td>
          <td class="px-4 py-3 border-b">{{ user.email }}</td>
          <td class="px-4 py-3 border-b">{{ user.is_admin ? 'Sim' : 'Não' }}</td>
          <td class="px-4 py-3 border-b">{{ formatarData(user.created_at) }}</td>
          <td class="px-4 py-3 border-b text-center space-x-2">
            <button @click="verDetalhes(user.id)" class="text-blue-600 hover:underline">Ver</button>
            <button @click="editarUsuario(user)" class="text-yellow-600 hover:underline">Editar</button>
            <button @click="excluirUsuario(user.id)" class="text-red-600 hover:underline">Excluir</button>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-else class="text-center text-gray-500 mt-8">Nenhum usuário encontrado.</div>

    <!-- Paginação -->
    <div class="mt-4 flex justify-center space-x-2" v-if="totalPaginas > 1">
      <button
        @click="paginaAtual--"
        :disabled="paginaAtual === 1"
        class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50"
      >
        Anterior
      </button>
      <span class="px-3 py-1">Página {{ paginaAtual }} de {{ totalPaginas }}</span>
      <button
        @click="paginaAtual++"
        :disabled="paginaAtual === totalPaginas"
        class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50"
      >
        Próxima
      </button>
    </div>

    <!-- Modal Criar/Editar -->
    <div v-if="modalAberto" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded shadow w-full max-w-md">
        <h2 class="text-xl font-semibold mb-4">{{ editando ? 'Editar Usuário' : 'Novo Usuário' }}</h2>
        <form @submit.prevent="salvarUsuario">
          <input v-model="form.name" placeholder="Nome" class="w-full border rounded p-2 mb-3" required />
          <input v-model="form.email" placeholder="Email" class="w-full border rounded p-2 mb-3" required />
          <input v-if="!editando" v-model="form.password" type="password" placeholder="Senha" class="w-full border rounded p-2 mb-3" required />
          <label class="flex items-center space-x-2 mb-4">
            <input type="checkbox" v-model="form.is_admin" />
            <span>É administrador?</span>
          </label>
          <div class="text-right">
            <button type="button" @click="fecharModal" class="mr-2 px-4 py-2 border rounded">Cancelar</button>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              {{ editando ? 'Salvar Alterações' : 'Criar Usuário' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Detalhes -->
    <div v-if="detalhes" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded shadow w-full max-w-md">
        <h2 class="text-xl font-semibold mb-2">📋 Detalhes do Usuário</h2>
        <p><strong>Nome:</strong> {{ detalhes.name }}</p>
        <p><strong>Email:</strong> {{ detalhes.email }}</p>
        <p><strong>Admin:</strong> {{ detalhes.is_admin ? 'Sim' : 'Não' }}</p>
        <p><strong>Criado em:</strong> {{ formatarData(detalhes.created_at) }}</p>

        <div class="mt-4">
          <canvas id="graficoTarefas" width="300" height="300"></canvas>
        </div>

        <div class="text-right mt-4">
          <button @click="fecharDetalhes" class="px-4 py-2 border rounded">Fechar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import Chart from 'chart.js/auto'

export default {
  name: 'AdminUsers',
  data() {
    return {
      usuarios: [],
      modalAberto: false,
      detalhes: null,
      editando: false,
      form: { name: '', email: '', password: '', is_admin: false },
      usuarioEditandoId: null,
      paginaAtual: 1,
      porPagina: 10,
      filtroNome: ''
    }
  },
  computed: {
    usuariosFiltrados() {
      return this.usuarios.filter(user =>
        user.name.toLowerCase().includes(this.filtroNome.toLowerCase())
      )
    },
    usuariosPaginados() {
      const inicio = (this.paginaAtual - 1) * this.porPagina
      return this.usuariosFiltrados.slice(inicio, inicio + this.porPagina)
    },
    totalPaginas() {
      return Math.ceil(this.usuariosFiltrados.length / this.porPagina) || 1
    }
  },
  methods: {
    async carregarUsuarios() {
      const response = await axios.get('/api/admin/users')
      this.usuarios = response.data
      if (this.paginaAtual > this.totalPaginas) {
        this.paginaAtual = this.totalPaginas
      }
    },
    abrirModalCriar() {
      this.modalAberto = true
      this.editando = false
      this.form = { name: '', email: '', password: '', is_admin: false }
    },
    editarUsuario(user) {
      this.modalAberto = true
      this.editando = true
      this.usuarioEditandoId = user.id
      this.form = { name: user.name, email: user.email, password: '', is_admin: user.is_admin }
    },
    async salvarUsuario() {
      try {
        this.form.is_admin = !!this.form.is_admin

        if (!this.editando && (!this.form.password || this.form.password.length < 6)) {
          alert('A senha é obrigatória e deve ter pelo menos 6 caracteres.')
          return
        }

        if (this.editando) {
          await axios.put(`/api/admin/users/${this.usuarioEditandoId}`, this.form)
        } else {
          await axios.post('/api/admin/users', this.form)
        }

        this.fecharModal()
        this.carregarUsuarios()
      } catch (error) {
        console.error('Erro ao salvar usuário:', error.response?.data ?? error)
        alert('Erro ao salvar usuário. Verifique os dados.')
      }
    },
    async excluirUsuario(id) {
      if (confirm('Tem certeza que deseja excluir este usuário?')) {
        await axios.delete(`/api/admin/users/${id}`)
        this.carregarUsuarios()
      }
    },
    fecharModal() {
      this.modalAberto = false
      this.usuarioEditandoId = null
    },
    async verDetalhes(id) {
      const res = await axios.get(`/api/admin/users/${id}`)
      this.detalhes = res.data
      this.$nextTick(() => {
        const ctx = document.getElementById('graficoTarefas')
        new Chart(ctx, {
          type: 'pie',
          data: {
            labels: ['Feitas', 'Pendentes'],
            datasets: [{
              label: 'Tarefas',
              data: [this.detalhes.feitas, this.detalhes.pendentes],
              backgroundColor: ['#16a34a', '#dc2626']
            }]
          }
        })
      })
    },
    fecharDetalhes() {
      this.detalhes = null
    },
    formatarData(dataStr) {
      const data = new Date(dataStr)
      const dia = String(data.getDate()).padStart(2, '0')
      const mes = String(data.getMonth() + 1).padStart(2, '0') // meses começam do 0
      const ano = data.getFullYear()
      const horas = String(data.getHours()).padStart(2, '0')
      const minutos = String(data.getMinutes()).padStart(2, '0')
      return `${dia}/${mes}/${ano} ${horas}:${minutos}`
    }

  },
  mounted() {
    this.carregarUsuarios()
  }
}
</script>

<style scoped>
table th, table td {
  text-align: left;
}
</style>
