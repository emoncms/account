<?php
defined('EMONCMS_EXEC') or die('Restricted access');
global $path;
?>
<style>
    /*.content-container {
        max-width: 980px;
    }*/
</style>
<?php load_js("Lib/js/vue.global.prod-3.5.22.min.js"); ?>

<div id="app">
    <h2><?php echo _("My Accounts"); ?></h2>
    
    <p>Multi-account administration.</p>


    <div class="input-prepend input-append" style="float:right">
        <button class="btn" id="open-add-user-modal"><i class="icon icon-plus"></i> <?php echo _("Add new user"); ?></button>
    </div>
    
    <div class="input-prepend" style="float:right">
        <span class="add-on">Filter</span>
        <input type="text" v-model="filterKey" style="width:120px; margin-right:20px" />
    </div>

    <p><b><?php echo _("Number of users:"); ?></b> {{accounts.length}}</p>
    <br>

    <div v-if="accounts.length==0" class="alert alert-warning"><?php echo _("Multi user management. Click on add new user to create a new user or to add an existing user."); ?></div>
    <table class="table table-striped" v-else>
        <tr>
            <th><?php echo _("Edit"); ?></th>
            <th @click="sort('id', 'asc')" style="cursor:pointer"><?php echo _("Id"); ?></th>
            <th @click="sort('username', 'asc')" style="cursor:pointer"><?php echo _("Username"); ?></th>
            <th @click="sort('location', 'asc')" style="cursor:pointer"><?php echo _("Location"); ?></th>
            <th @click="sort('email', 'asc')" style="cursor:pointer"><?php echo _("Email"); ?></th>
            <th @click="sort('access', 'asc')" style="cursor:pointer"><?php echo _("User Login"); ?></th>
            <th @click="sort('diskuse', 'asc')" style="cursor:pointer"><?php echo _("Diskuse"); ?></th>
            <th @click="sort('activefeeds', 'asc')" style="cursor:pointer"><?php echo _("Feeds"); ?></th>
            <th></th>
            <th></th>
        </tr>
        <tr v-for="(user,index) in fAccounts" :class="{
            'success': user.activefeeds / user.feeds > 0.8, 
            'error': user.activefeeds / user.feeds < 0.2,
            'warning': user.activefeeds / user.feeds >= 0.2 && user.activefeeds / user.feeds <= 0.8
        }">
            <td><a class="btn btn-info btn-sm" :href="path+'account/switch?userid='+user.id">view</a></td>
            <td>{{ user.id }}</td>
            <td>{{ user.username }}</td>
            <td>{{ user.location }}</td>
            <td>{{ user.email }}</td>
            <td @click="change_access(index)" style="cursor:pointer" title="Click to change access level">
                <span class="label label-inverse" v-if="user.access==0">Disabled</span>
                <span class="label label-warning" v-if="user.access==1">Read only</span>
                <span class="label label-success" v-if="user.access==2">Write access</span>
            </td>
            <td>{{ diskuse(user.diskuse) }}</td>
            <td><b><span style="color:#468847">{{ user.activefeeds }}</span>/{{ user.feeds }}</b></td>
            <td @click="edit(index)" style="cursor:pointer"><i class="icon-pencil"></i></td>
            <td @click="unlink(index)" style="cursor:pointer"><i class="icon-trash"></i></td>
        </tr>
    </table>

    <div class="input-prepend">
        <button class="btn" @click="refresh"><i class="icon-refresh"></i> <?php echo _("Refresh disk use and active feeds"); ?></button>
    </div>

    <div id="addNewUserModal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="addNewUserModalLabel" aria-hidden="true" data-backdrop="static" style="width:300px">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="addNewUserModalLabel">Add new user</h3>
        </div>
        <div class="modal-body">

            <p>
                <lable>Username:</label><br>
                <input v-model="add_username" type="text" style="width:250px" />
            </p>
            <p>
                <lable>Password:</label><br>
                <input v-model="add_password" type="text" style="width:250px" />
            </p>
            <p>
                <lable>Email:</label><br>
                <input v-model="add_email" type="text" style="width:250px" />
            </p>
            <p>
                <lable>Timezone:</label><br>
                <input v-model="add_timezone" type="text" style="width:250px" />
            </p>

            <div class="alert alert-error" v-if="add_error" style="margin-bottom:0px">{{ add_error }}</div>

        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo _('Close'); ?></button>
            <button class="btn btn-info"  @click="add_account"><?php echo _('Add user'); ?></button>
        </div>
    </div>

    <!-- Edit user modal -->
    <div id="editUserModal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true" data-backdrop="static" style="max-width:600px">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="editUserModalLabel">Edit User</h3>
        </div>
        <div class="modal-body">
            <div class="input-prepend input-append">
                <span class="add-on" style="width:150px">Location:</span>
                <input v-model="edit_location" type="text" style="width:200px" />
                <button class="btn btn-primary" @click="update_location">Save</button>
            </div>

            <div class="input-prepend input-append">
                <span class="add-on" style="width:150px">Email:</span>
                <input v-model="edit_email" type="text" style="width:200px" />
                <button class="btn btn-primary" @click="update_email">Save</button>
            </div>

            <br>

            <div class="input-prepend input-append">
                <span class="add-on" style="width:150px">Password:</span>
                <input v-model="edit_password" type="text" style="width:200px" />
                <button class="btn" @click="generate_password">Generate</button>
                <button class="btn btn-primary" @click="update_password">Save</button>
            </div>
            

            <div class="alert alert-error" v-if="edit_error" style="margin-bottom:0px">{{ edit_error }}</div>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo _('Close'); ?></button>
        </div>
    </div>
</div>

<script>
    // Vue app
    var app = Vue.createApp({
        data() { return {
            path: "<?php echo $path; ?>",
            accounts: [],
            user: {},
            // Add
            add_username: "",
            add_password: "",
            add_email: "",
            add_timezone: "",
            add_error: false,
            // Edit
            edit_userid: 0,
            edit_location: "",
            edit_email: "",
            edit_password: "",
            edit_error: false,
            // Sort
            currentSortColumn: 'id',
            currentSortDir: 'desc',
            filterKey: ''
        }; },
        mounted: function() {
            this.getAccounts();
            this.getUser();

            // refresh after 3 seconds
            setTimeout(function() {
                app.refresh();
            }, 1000);
        },
        methods: {
            getAccounts: function() {
                // using jquery ajax
                $.ajax({
                    url: path + "account/list.json",
                    dataType: 'json',
                    success: function(result) {
                        app.accounts = result;
                    }
                });
            },
            getUser: function() {
                // using jquery ajax
                $.ajax({
                    url: path + "user/get.json",
                    dataType: 'json',
                    success: function(result) {
                        app.user = result;
                        app.add_email = app.user.email;
                        app.add_timezone = app.user.timezone;
                    }
                });
            },
            add_account: function() {
                $.ajax({
                    type: "POST",
                    url: path + "account/add.json",
                    dataType: 'json',
                    data: {
                        username: encodeURIComponent(app.add_username),
                        password: encodeURIComponent(app.add_password),
                        email: encodeURIComponent(app.add_email),
                        timezone: encodeURIComponent(app.add_timezone)
                    },
                    success: function(result) {
                        if (result.success) {
                            app.getAccounts();
                            $('#addNewUserModal').modal('hide');
                        } else {
                            app.add_error = result.message;
                        }
                    }
                });
            },
            edit : function(index) {
                app.edit_userid = app.accounts[index].id;
                app.edit_location = app.accounts[index].location;
                app.edit_email = app.accounts[index].email;
                app.edit_error = false;
                $('#editUserModal').modal('show');
            },
            update_location: function() {
                $.ajax({
                    type: "POST",
                    url: path + "account/location.json",
                    dataType: 'json',
                    data: {
                        userid: app.edit_userid,
                        location: app.edit_location
                    },
                    success: function(result) {
                        if (result.success) {
                            app.getAccounts();
                            $('#editUserModal').modal('hide');
                        } else {
                            app.edit_error = result.message;
                        }
                    }
                });
            },
            update_email: function() {
                $.ajax({
                    type: "POST",
                    url: path + "account/email.json",
                    dataType: 'json',
                    data: {
                        userid: app.edit_userid,
                        email: app.edit_email
                    },
                    success: function(result) {
                        if (result.success) {
                            app.getAccounts();
                            $('#editUserModal').modal('hide');
                        } else {
                            app.edit_error = result.message;
                        }
                    }
                });
            },
            generate_password: function() {
                var length = 16;
                var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                var retVal = "";
                for (var i = 0, n = charset.length; i < length; ++i) {
                    retVal += charset.charAt(Math.floor(Math.random() * n));
                }
                app.edit_password = retVal;
            },
            update_password: function() {
                $.ajax({
                    type: "POST",
                    url: path + "account/password.json",
                    dataType: 'json',
                    data: {
                        userid: app.edit_userid,
                        password: encodeURIComponent(app.edit_password)
                    },
                    success: function(result) {
                        if (result.success) {
                            alert("Password updated");
                            app.getAccounts();
                            $('#editUserModal').modal('hide');
                        } else {
                            app.edit_error = result.message;
                        }
                    }
                });
            },
            change_access: function(index) {
                var userid = app.accounts[index].id;
                var access = app.accounts[index].access;
                access ++;
                if (access > 2) access = 0;
                $.ajax({
                    type: "POST",
                    url: path + "account/setaccess.json",
                    dataType: 'json',
                    data: {
                        userid: userid,
                        access: access
                    },
                    success: function(result) {
                        app.accounts[index].access = access;
                    }
                });
            },
            unlink: function(index) {
                var userid = app.accounts[index].id;

                // ask for confirmation
                if (!confirm("Are you sure you want to unlink this user? Original user will not be deleted")) return;

                $.ajax({
                    type: "POST",
                    url: path + "account/unlink.json",
                    dataType: 'json',
                    data: {
                        userid: userid
                    },
                    success: function(result) {
                        app.getAccounts();
                    }
                });
            },
            sort: function(column, starting_order) {

                if (this.currentSortColumn != column) {
                    this.currentSortDir = starting_order;
                    this.currentSortColumn = column;
                } else {
                    if (this.currentSortDir == 'desc') {
                        this.currentSortDir = 'asc';
                    } else {
                        this.currentSortDir = 'desc';
                    }
                }
                this.sort_only(column);
            },
            sort_only: function(column) {
                this.accounts.sort((a, b) => {
                    let modifier = 1;
                    if (this.currentSortDir == 'desc') modifier = -1;
                    if (a[column] < b[column]) return -1 * modifier;
                    if (a[column] > b[column]) return 1 * modifier;
                    return 0;
                });
            },
            filterAccounts: function (row) {
                if (this.filterKey != '') {
                    return Object.keys(row).some((key) => {
                        return String(row[key]).toLowerCase().indexOf(this.filterKey.toLowerCase()) > -1
                    })
                }
                return true;
            },
            refresh: function() {
                $.ajax({
                    type: "GET",
                    url: path + "account/refresh.json",
                    dataType: 'json',
                    success: function(result) {
                        app.getAccounts();
                    }
                });
            },
            diskuse: function(value) {
                let mb = value / (1024*1024);
                if (mb>10) {
                    return mb.toFixed(0)+" MB";
                } else {
                    return mb.toFixed(1)+" MB";
                }
            }
        },
        computed: {
            fAccounts: function () {
                return this.accounts.filter(this.filterAccounts);
            }
        }
    }).mount('#app');

    $("#open-add-user-modal").click(function() {
        app.add_error = false;
        $('#addNewUserModal').modal('show');
    });
</script>
