<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'

const tarefas = ref([])
const carregando = ref(true)
const erro = ref(null)

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

onMounted(() => {
  buscarTarefas()
})
</script>

<template>
  <AppLayout title="Minhas Tarefas">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Minhas Tarefas</h2>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
          <div v-if="carregando">Carregando tarefas...</div>
          <div v-else-if="erro" class="text-red-600">{{ erro }}</div>
          <div v-else-if="tarefas.length === 0" class="text-gray-500">Nenhuma tarefa encontrada.</div>
          
          <ul v-else class="space-y-3">
            <li v-for="tarefa in tarefas" :key="tarefa.id" class="border-b pb-2">
              <div class="flex justify-between items-center">
                <div>
                  <h3 class="text-lg font-bold">{{ tarefa.title }}</h3>
                  <p class="text-sm text-gray-600" v-if="tarefa.due_date">Vencimento: {{ tarefa.due_date }}</p>
                </div>
                <span :class="tarefa.is_completed ? 'text-green-600' : 'text-yellow-600'">
                  {{ tarefa.is_completed ? 'Concluída' : 'Pendente' }}
                </span>
              </div>
              <p class="mt-1 text-gray-700">{{ tarefa.description }}</p>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
