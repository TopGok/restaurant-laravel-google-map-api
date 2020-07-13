<template>
  <table class="table table-dark">
    <thead>
      <tr>
        <th v-for="headerTable in headerTables" :key="headerTable">{{headerTable}}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="data in datas" :key="data.id">
        <th>{{data.id}}</th>
        <th>{{data.name}}</th>
        <td>{{data.address}}</td>
        <td>{{data.location.lat}}</td>
        <td>{{data.location.lng}}</td>
      </tr>
    </tbody>
  </table>
</template>

<script>
export default {
  name: "Table",
  props: {
    datas: Array,
    headerTables: Array
  }
};
</script>