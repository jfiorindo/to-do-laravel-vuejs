<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'
import { Pie } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, ArcElement)

const tarefas = ref([])
const carregando = ref(true)
const erro = ref(null)
const user = usePage().props.auth.user
const novaSenha = ref('')
const showModal = ref(false)
const senhaAtual = ref('')
const confirmacaoSenha = ref('')
const erroSenha = ref('')
const sucessoSenha = ref('')
const mostrarSenhaAtual = ref(false)
const mostrarNovaSenha = ref(false)


const buscarTarefas = async () => {
  try {
    const response = await axios.get('/api/tasks')
    tarefas.value = response.data
  } catch (err) {
    erro.value = 'Erro ao carregar tarefas'
    console.error(err)
  } finally {
    carregando.value = false
  }
}

const alternarStatus = async (tarefa) => {
  try {
    await axios.put(`/api/tasks/${tarefa.id}`, {
      ...tarefa,
      is_completed: !tarefa.is_completed,
    })
    tarefa.is_completed = !tarefa.is_completed
  } catch (err) {
    console.error('Erro ao atualizar status da tarefa:', err)
  }
}

const logout = () => {
  router.post(route('logout'), {}, {
    onFinish: () => router.visit('/login')
  })
}

const hoje = new Date().toISOString().split('T')[0]

const tarefasPendentes = computed(() =>
  tarefas.value.filter(t => !t.is_completed && t.due_date >= hoje)
)

const tarefasConcluidas = computed(() =>
  tarefas.value.filter(t => t.is_completed && t.due_date >= hoje)
)

const tarefasVencidas = computed(() => {
  const hoje = new Date().toISOString().split('T')[0]
  return tarefas.value.filter(t => t.due_date < hoje)
})

const chartData = computed(() => ({
  labels: ['Pendentes', 'Concluídas'],
  datasets: [
    {
      data: [tarefasPendentes.value.length, tarefasConcluidas.value.length],
      backgroundColor: ['#dc2626', '#16a34a'],
    },
  ],
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom'
    }
  }
}

onMounted(() => {
  buscarTarefas()
})

const exportarCSV = async () => {
  try {
    const response = await axios.get('/api/export-tasks', {
      responseType: 'blob',
    })

    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.setAttribute('download', 'tarefas.csv')
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (error) {
    console.error('Erro ao exportar tarefas:', error)
  }
}

const alterarSenha = async () => {
  try {
    await axios.post('/api/user/password', {
      senhaAtual: senhaAtual.value,
      novaSenha: novaSenha.value,
    })
    alert('Senha alterada com sucesso!')
    showModal.value = false
    senhaAtual.value = ''
    novaSenha.value = ''
  } catch (error) {
    console.error(error.response?.data || error)
    alert('Erro ao alterar senha. Verifique os dados informados.')
  }
}





</script>

<template>
  <Head title="Dashboard" />
  <AppLayout title="Minhas Tarefas">
    <template #header>
      <div class="flex justify-between items-center">
        <div class="text-gray-700 font-semibold">Bem-vindo, {{ user.name }}</div>
        <div class="space-x-2">
          <button @click="showModal = true" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
            Meu Perfil
          </button>
          <button @click="logout" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
            Logout
          </button>
        </div>
      </div>
    </template>
    <!-- Modal de alteração de senha -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h2 class="text-lg font-bold mb-4">Alterar Senha</h2>

        <p class="text-sm text-gray-700 mb-2"><strong>Nome:</strong> {{ user.name }}</p>
        <p class="text-sm text-gray-700 mb-4"><strong>Email:</strong> {{ user.email }}</p>

        <label class="block mb-2 text-sm font-medium text-gray-700">Senha Atual</label>
          <div class="relative mb-4">
            <input
              :type="mostrarSenhaAtual ? 'text' : 'password'"
              v-model="senhaAtual"
              class="w-full p-2 border rounded pr-10"
              placeholder="Digite sua senha atual"
            />
            <button
              type="button"
              @click="mostrarSenhaAtual = !mostrarSenhaAtual"
              class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500"
              tabindex="-1"
            >
              {{ mostrarSenhaAtual ? '🔐' : '🔓' }}
            </button>
          </div>

        <label class="block mb-2 text-sm font-medium text-gray-700">Nova Senha</label>
          <div class="relative mb-4">
            <input
              :type="mostrarNovaSenha ? 'text' : 'password'"
              v-model="novaSenha"
              class="w-full p-2 border rounded pr-10"
              placeholder="Digite a nova senha"
            />
            <button
              type="button"
              @click="mostrarNovaSenha = !mostrarNovaSenha"
              class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500"
              tabindex="-1"
            >
              {{ mostrarNovaSenha ? '🔐' : '🔓' }}
            </button>
          </div>

        <div class="flex justify-end space-x-2">
          <button @click="showModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancelar</button>
          <button @click="alterarSenha" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Salvar</button>
        </div>
      </div>
    </div>
    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">
        <div v-if="carregando" class="text-gray-600">Carregando tarefas...</div>
        <div v-else-if="erro" class="text-red-600">{{ erro }}</div>
        <div v-else>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Tarefas pendentes -->
            <div>
              <h3 class="text-xl font-bold mb-4 text-red-600">Tarefas Pendentes</h3>
              <div v-if="tarefasPendentes.length === 0" class="text-gray-500">Nenhuma tarefa pendente.</div>
              <div v-for="tarefa in tarefasPendentes" :key="tarefa.id" class="bg-white p-4 rounded shadow mb-4">
                <div class="flex justify-between items-start">
                  <div>
                    <h4 class="text-lg font-semibold text-gray-800">{{ tarefa.title }}</h4>
                    <p class="text-sm text-gray-600">{{ tarefa.description }}</p>
                    <p class="text-sm text-gray-400 mt-1">Vencimento: {{ tarefa.due_date }}</p>
                  </div>
                  <input type="checkbox" :checked="tarefa.is_completed" @change="() => alternarStatus(tarefa)" class="w-5 h-5">
                </div>
              </div>
            </div>

            <!-- Tarefas concluídas -->
            <div>
              <h3 class="text-xl font-bold mb-4 text-green-600">Tarefas Concluídas</h3>
              <div v-if="tarefasConcluidas.length === 0" class="text-gray-500">Nenhuma tarefa concluída.</div>
              <div v-for="tarefa in tarefasConcluidas" :key="tarefa.id" class="bg-white p-4 rounded shadow mb-4 border border-green-200">
                <div class="flex justify-between items-start">
                  <div>
                    <h4 class="text-lg font-semibold text-gray-800">{{ tarefa.title }}</h4>
                    <p class="text-sm text-gray-600">{{ tarefa.description }}</p>
                    <p class="text-sm text-gray-400 mt-1">Vencimento: {{ tarefa.due_date }}</p>
                  </div>
                  <input type="checkbox" :checked="tarefa.is_completed" @change="() => alternarStatus(tarefa)" class="w-5 h-5">
                </div>
              </div>
            </div>
          </div>

          <!-- Gráfico -->
          <div class="bg-white rounded shadow p-6 max-w-md mx-auto">
            <h3 class="text-lg font-bold mb-4 text-center">Resumo de Tarefas</h3>
            <div style="height: 250px">
              <Pie :data="chartData" :options="chartOptions" />
            </div>
          </div>

          <!-- Tarefas vencidas -->
          <div class="bg-white rounded shadow p-6">
            <h3 class="text-lg font-bold mb-4 text-yellow-600">Tarefas Vencidas</h3>
            <div v-if="tarefasVencidas.length === 0" class="text-gray-500">Nenhuma tarefa vencida.</div>
            <table v-else class="w-full table-auto border text-sm">
              <thead class="bg-gray-100">
                <tr>
                  <th class="border px-4 py-2">Título</th>
                  <th class="border px-4 py-2">Vencimento</th>
                  <th class="border px-4 py-2">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tarefa in tarefasVencidas" :key="tarefa.id">
                  <td class="border px-4 py-2">{{ tarefa.title }}</td>
                  <td class="border px-4 py-2">{{ tarefa.due_date }}</td>
                  <td class="border px-4 py-2">
                    <span :class="tarefa.is_completed ? 'text-green-600' : 'text-red-600'">
                      {{ tarefa.is_completed ? 'Concluída' : 'Pendente' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="flex justify-center my-6">
          <button
            @click="exportarCSV"
            class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-6 rounded shadow transition">
            Exportar Tarefas (.CSV)
          </button>
        </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
