<script setup>
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

</script>

<template>
  <AppLayout title="Minhas Tarefas">
    <template #header>
      <div class="flex justify-between items-center">
        <div class="text-gray-700 font-semibold">Bem-vindo, {{ user.name }}</div>
        <button @click="logout" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
          Logout
        </button>
      </div>
    </template>

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
