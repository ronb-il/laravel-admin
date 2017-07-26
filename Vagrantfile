Vagrant.configure(2) do |config|

  config.vm.box = "personali/generic"
  
  config.vm.box_url = "http://vagrant.personali.org/repo/generic/"

  config.ssh.username = "personali"
  config.ssh.private_key_path = "vagrant/private_key"

  config.vm.network "private_network", type: "dhcp"
  config.vm.network "forwarded_port", guest: 80, host: 80
  config.vm.network "forwarded_port", guest: 3606, host: 3606
  config.vm.network "forwarded_port", guest: 8081, host: 8081

  #Apply this patch if you're using vagrant 1.8.1 on Windows https://github.com/mitchellh/vagrant/pull/6741/files
  config.vm.provision "ansible_local" do |ansible_local|
    ansible_local.playbook = "./vagrant/provisioning/all.yml"
    ansible_local.sudo = false
  end

  config.vm.provision "shell", run: "always" do |shell|
    shell.inline = "service httpd restart"
  end

  config.vm.provision "shell", run: "always" do |shell|
    shell.inline = "/etc/init.d/affiliate start"
  end

  config.vm.synced_folder "..", "/opt/personali/workspace/", type: "nfs"
  config.vm.synced_folder ".", "/vagrant", type: "nfs"

  config.shortcuts.add(:"cockpit-restart", start_machine: false) do |machine|
    machine.action(:ssh_run, ssh_run_command: <<-cmd)
      sudo service httpd restart
    cmd
  end

  config.shortcuts.add(:"affiliate-restart", start_machine: false) do |machine|
    machine.action(:ssh_run, ssh_run_command: <<-cmd)
      sudo /etc/init.d/affiliate restart
    cmd
  end

end
