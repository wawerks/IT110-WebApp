<template>
  <v-container class="pa-6">
    <v-card class="mx-auto" max-width="1500">
      <v-data-table :headers="headers" :items="filteredLogs" :search="search" :loading="loading" :items-per-page="10"
        class="elevation-1" v-model:page="page">
        <template v-slot:top>
          <div class="pa-4">
            <div class="text-h5 text-center mb-4">Users Activity Log</div>
            <div class="d-flex justify-start">
              <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" single-line hide-details
                style="max-width: 300px;"></v-text-field>
            </div>
          </div>
        </template>

        <template v-slot:header="{ props }">
          <tr>
            <th class="text-left">User</th>
            <th class="text-left">Action</th>
            <th class="text-left">Time</th>
            <th class="text-left">IP Address</th>
          </tr>
        </template>

        <template v-slot:header.name>
          <div>
            <div class="text-subtitle-2 font-weight-bold">User</div>
            <div class="text-caption text-grey">Name of user</div>
          </div>
        </template>

        <template v-slot:header.action>
          <div>
            <div class="text-subtitle-2 font-weight-bold">Action</div>
            <div class="text-caption text-grey">Activity performed</div>
          </div>
        </template>

        <template v-slot:header.action_time>
          <div>
            <div class="text-subtitle-2 font-weight-bold">Time</div>
            <div class="text-caption text-grey">When performed</div>
          </div>
        </template>

        <template v-slot:header.ip_address>
          <div>
            <div class="text-subtitle-2 font-weight-bold">IP Address</div>
            <div class="text-caption text-grey">IP address of user</div>
          </div>
        </template>

        <template v-slot:item="{ item }">
          <tr>
            <td>{{ item.name }}</td>
            <td>{{ item.action }}</td>
            <td>{{ formatDate(item.action_time) }}</td>
            <td>{{ item.ip_address }}</td>
          </tr>
        </template>

        <template v-slot:progress>
          <v-progress-linear color="#4fb9af" height="2" indeterminate></v-progress-linear>
        </template>

        <template v-slot:bottom>
          <div class="d-flex align-center justify-center pa-4 gap-4">
            <v-btn style="background-color: #4fb9af; color: white; text-align: center" variant="flat"
              :disabled="page === 1" @click="page--">
              Previous
            </v-btn>
            <span>Page {{ page }} of {{ Math.ceil(filteredLogs.length / 10) }}</span>
            <v-btn style="background-color: #4fb9af; color: white; text-align: center" variant="flat"
              :disabled="page >= Math.ceil(filteredLogs.length / 10)" @click="page++">
              Next
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>
  </v-container>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UsersLog',
  data() {
    return {
      search: '',
      page: 1,
      headers: [
        {
          text: 'User',
          value: 'name',
          width: '20%',
          align: 'start',
          sortable: true,
          filterable: true,
        },
        {
          text: 'Action',
          value: 'action',
          width: '30%',
          align: 'start',
          sortable: true,
          filterable: true,
        },
        {
          text: 'Time',
          value: 'action_time',
          width: '20%',
          align: 'start',
          sortable: true,
        },
        {
          text: 'IP Address',
          value: 'ip_address',
          width: '30%',
          align: 'start',
          sortable: true,
          filterable: true,
        },
      ],
      logs: [],
      loading: true,
    };
  },
  computed: {
    filteredLogs() {
      if (!this.search) return this.logs;
      const searchTerm = this.search.toLowerCase();
      return this.logs.filter(log =>
        log.name.toLowerCase().includes(searchTerm) ||
        log.action.toLowerCase().includes(searchTerm) ||
        this.formatDate(log.action_time).toLowerCase().includes(searchTerm) ||
        log.ip_address.toLowerCase().includes(searchTerm)
      );
    }
  },
  created() {
    this.fetchLogs();
  },
  methods: {
    fetchLogs() {
      this.loading = true;
      axios.get('/admin/users-log')
        .then(response => {
          console.log('Response data:', response.data);
          if (response.data && response.data.logs) {
            this.logs = response.data.logs;
          } else {
            this.logs = [];
          }
          this.loading = false;
        })
        .catch(error => {
          console.error('Error fetching logs:', error);
          this.logs = [];
          this.loading = false;
        });
    },
    formatDate(dateString) {
      const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      };
      const date = new Date(dateString);
      return date.toLocaleString('en-US', options);
    },
  },
};
</script>

<style scoped>
.gap-4 {
  gap: 1rem;
}

.v-data-table {
  background: white;
  border-radius: 0 0 4px 4px;
}

:deep(.v-data-table-header th) {
  color: black !important;
  font-weight: bold !important;
  font-size: 1rem !important;
  text-transform: none !important;
  background-color: #f5f5f5 !important;
  padding: 12px 16px !important;
}

:deep(.v-data-table tbody td) {
  color: black !important;
  padding: 12px 16px !important;
}

:deep(.v-data-table tbody tr:nth-child(even)) {
  background-color: #f9f9f9;
}

:deep(.v-data-table tbody tr:hover) {
  background-color: #f0f0f0;
}
</style>