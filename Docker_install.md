# Installation de Docker Desktop (Windows)

## 1) Télécharger
- Installeur : [Docker Desktop pour Windows](https://desktop.docker.com/win/main/amd64/Docker%20Desktop%20Installer.exe?utm_source=docker&utm_medium=webreferral&utm_campaign=dd-smartbutton&utm_location=module)

### Puis `wsl --update`
### Et enfin redemarrez l'ordinateur


# Sinon:
## 2) Prérequis (WSL 2 / Virtualisation)
- Windows 10/11 64-bits
- Virtualisation activée dans le BIOS/UEFI
- WSL 2 (recommandé)  
  Ouvrez **PowerShell en administrateur** et exécutez :
  ```powershell
  dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart
  dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart
  wsl --install
  wsl --set-default-version 2
