<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Liste des CPUs</h1>
    <a :href="route('cpus.create')" class="mb-4 inline-block bg-green-600 text-white px-4 py-2 rounded">Ajouter un CPU</a>
    <table>
      <thead>
        <tr>
          <th>Nom</th>
          <th>Marque</th>
          <th>Socket</th>
          <th>Cœurs</th>
          <th>Threads</th>
          <th>Base Clock</th>
          <th>Boost Clock</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="cpu in cpus" :key="cpu.id">
          <td>{{ cpu.component?.name }}</td>
          <td>{{ cpu.component?.brand }}</td>
          <td>{{ cpu.socket }}</td>
          <td>{{ cpu.core_count }}</td>
          <td>{{ cpu.thread_count }}</td>
          <td>{{ cpu.base_clock }} GHz</td>
          <td>{{ cpu.boost_clock }} GHz</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({ cpus: Array });
</script>
