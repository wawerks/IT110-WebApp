<template>
  <v-container class="pa-6">
    <v-card class="mx-auto" max-width="1500">
      <v-card-title class="headline d-flex align-center">
        <v-spacer></v-spacer>
        <span>Users Management</span>
        <v-spacer></v-spacer>
        <v-btn style="background-color: #4fb9af; color: white" variant="flat" class="ml-2 d-flex align-center"
          @click="showAddDialog = true">
          <span>Add User</span>
        </v-btn>
      </v-card-title>
      <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" single-line hide-details class="mx-4"
        style="max-width: 300px;"></v-text-field>
      <v-divider></v-divider>
      <v-data-table :headers="headers" :items="filteredUsers" :search="search" :loading="loading" :items-per-page="10"
        class="elevation-1" v-model:page="page">
        <template v-slot:progress>
          <v-progress-linear color="#4fb9af" height="2" indeterminate></v-progress-linear>
        </template>
        <template v-slot:header.name>
          <div>
            <div class="text-subtitle-2 font-weight-bold">Name</div>
            <div class="text-caption text-grey">User's full name</div>
          </div>
        </template>

        <template v-slot:header.email>
          <div>
            <div class="text-subtitle-2 font-weight-bold">Email</div>
            <div class="text-caption text-grey">Contact email</div>
          </div>
        </template>

        <template v-slot:header.role>
          <div>
            <div class="text-subtitle-2 font-weight-bold">Role</div>
            <div class="text-caption text-grey">User access level</div>
          </div>
        </template>

        <template v-slot:header.created_at>
          <div>
            <div class="text-subtitle-2 font-weight-bold">Created</div>
            <div class="text-caption text-grey">Account creation date</div>
          </div>
        </template>

        <template v-slot:header.actions>
          <div>
            <div class="text-subtitle-2 font-weight-bold">Actions</div>
            <div class="text-caption text-grey">Available operations</div>
          </div>
        </template>
        <template v-slot:item.actions="{ item }">
          <div class="d-flex align-center">
            <font-awesome-icon icon="fa-solid fa-pen-to-square" class="mr-5 action-icon"
              style="color: #4fb9af; cursor: pointer;" @click="editUser(item)" />
            <font-awesome-icon icon="fa-solid fa-trash" class="action-icon" style="color: #FF5252; cursor: pointer;"
              @click="deleteUser(item)" />
          </div>
        </template>
        <template v-slot:bottom="props">
          <div class="d-flex align-center justify-center pa-4 gap-4">
            <v-btn style="background-color: #4fb9af; color: white; text-align: center" variant="flat"
              :disabled="page === 1" @click="page--">
              Previous
            </v-btn>
            <span>Page {{ page }} of {{ Math.ceil(filteredUsers.length / 10) }}</span>
            <v-btn style="background-color: #4fb9af; color: white; text-align: center" variant="flat"
              :disabled="page >= Math.ceil(filteredUsers.length / 10)" @click="page++">
              Next
            </v-btn>
          </div>
        </template>
        <template v-slot:item.created_at="{ item }">
          <td>{{ formatDate(item.created_at) }}</td>
        </template>
      </v-data-table>

      <!-- Add User Dialog -->
      <v-dialog v-model="showAddDialog" max-width="500px">
        <v-card>
          <v-card-title class="text-h5 pa-4">Add New User</v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-text-field label="Name" v-model="newUser.name" required></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field label="Email" v-model="newUser.email" required></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field label="Password" v-model="newUser.password" type="password" required></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-select label="Role" v-model="newUser.role" :items="roles" item-title="name" item-value="value"
                    required></v-select>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="grey" variant="text" @click="showAddDialog = false">
              Cancel
            </v-btn>
            <v-btn style="background-color: #4fb9af; color: white; text-align: center" variant="flat" @click="addUser">
              Save
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>


      <!-- Edit User Dialog -->
      <v-dialog v-model="showEditDialog" max-width="500px">
        <v-card>
          <v-card-title class="text-h5 pa-4">Edit User</v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-text-field label="Name" v-model="editedUser.name" required></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field label="Email" v-model="editedUser.email" required></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field label="Password" v-model="editedUser.password" type="password" required></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-select label="Role" v-model="editedUser.role" :items="roles" item-title="name" item-value="value"
                    required></v-select>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="grey" variant="text" @click="showEditDialog = false">
              Cancel
            </v-btn>
            <v-btn style="background-color: #4fb9af; color: white; text-align: center" variant="flat" @click="saveEdit">
              Save
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

    </v-card>
  </v-container>
</template>

<script>
import axios from 'axios';
import { ref } from 'vue';

export default {
  name: 'UsersView',
  data() {
    return {
      search: '',
      headers: [
        { text: 'Name', value: 'name', width: '25%' },
        { text: 'Email', value: 'email', width: '30%' },
        { text: 'Role', value: 'role', width: '15%' },
        { text: 'Created', value: 'created_at', width: '15%' },
        { text: 'Actions', value: 'actions', width: '10%', align: 'center', sortable: false },
      ],
      users: [],
      page: 1,
      loading: true,
      showAddDialog: false,
      newUser: {
        name: '',
        email: '',
        password: '',
        role: null,
      },
      roles: [
        { name: 'Admin', value: 'admin' },
        { name: 'User', value: 'user' },
      ],
      showEditDialog: false,
      editedUser: {},
    };
  },


  computed: {
    filteredUsers() {
      return this.users.filter(user =>
        user.name.toLowerCase().includes(this.search.toLowerCase()) ||
        user.email.toLowerCase().includes(this.search.toLowerCase())
      );
    },
  },

  created() {
    this.fetchUsers();
  },

  methods: {
    fetchUsers() {
      this.loading = true;
      axios
        .get('/admin/users')
        .then(response => {
          this.users = response.data.users.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        })
        .catch(error => {
          console.error('Error fetching users:', error);
        })
        .finally(() => {
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

    async addUser() {
      if (!this.newUser.name || !this.newUser.email || !this.newUser.role || !this.newUser.password) {
        alert('Please fill in all required fields.');
        return;
      }

      try {
        console.log('Adding user:', this.newUser);
        const response = await axios.post('/users', this.newUser);

        // Add the new user directly to the list
        this.users.unshift(response.data.user);
        this.fetchUsers(); // Optionally fetch to sync with the backend
        this.showAddDialog = false;
        this.newUser = { name: '', email: '', password: '', role: null }; // Reset the form
        alert('User added successfully!');
      } catch (error) {
        console.error('Error adding user:', error.response || error);
        const errorMessage = error.response?.data?.message || 'Failed to add user. Please try again.';
        alert(errorMessage);
      }
    },

    editUser(user) {
      this.editedUser = { ...user };
      this.showEditDialog = true;
    },

    saveEdit() {
      if (!this.editedUser.name || !this.editedUser.email) {
        alert('Please fill in all required fields.');
        return;
      }

      axios.put(`/users/${this.editedUser.id}/edit`, this.editedUser)
        .then(response => {
          const index = this.users.findIndex(user => user.id === this.editedUser.id);
          if (index !== -1) {
            this.users[index] = response.data; // Direct assignment in Vue 3
          }
          this.showEditDialog = false;
          alert('User updated successfully!');
        })
        .catch(error => {
          console.error('Error updating user:', error);
          alert(error.response?.data?.message || 'Failed to update user. Please try again.');
        });
    },

    deleteUser(user) {
      if (confirm(`Are you sure you want to delete ${user.name}?`)) {
        axios
          .delete(`/users/${user.id}`)
          .then(() => {
            this.users = this.users.filter(u => u.id !== user.id); // Remove the user from the list
            alert('User deleted successfully!');
          })
          .catch(error => {
            console.error('Error deleting user:', error);
            alert(error.response?.data?.message || 'Failed to delete user. Please try again.');
          });
      }
    },
  },
};
</script>



<style scoped>
.v-data-table {
  background: white;
  border-radius: 0 0 4px 4px;
}

.action-icon {
  font-size: 1.2rem;
}

.action-icon:hover {
  opacity: 0.8;
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
