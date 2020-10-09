alias cls='printf "\033c"'

export PS1='\[\e[1;32m\]\h\[\e[0m\] \[\e[1;37m\]\w\[\e[0m\] \[\e[1;32m\]\u\[\e[0m\] \[\e[1;37m\]\$\[\e[0m\] '

if ! pgrep ssh-agent > /dev/null
then
  eval `ssh-agent`
  rm -f ~/.ssh/ssh_auth_sock
  ln -sf "$SSH_AUTH_SOCK" ~/.ssh/ssh_auth_sock
fi
export SSH_AUTH_SOCK=~/.ssh/ssh_auth_sock
ssh-add -l > /dev/null || ssh-add

if [ -f ~/.bash_aliases ]; then
    . ~/.bash_aliases
fi