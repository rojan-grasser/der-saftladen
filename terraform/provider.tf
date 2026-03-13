terraform {
  required_providers {
    minio = {
      source  = "aminueza/minio"
      version = ">= 1.0"
    }
    local = {
      source  = "hashicorp/local"
      version = ">= 2.0"
    }
  }
}

provider "minio" {
  minio_server   = var.minio_host
  minio_user     = var.minio_user
  minio_password = var.minio_password
}
