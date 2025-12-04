# Kubernetes Deployment

## Configuration

Copy `secrets.example.yaml` to `secrets.yaml` and replace the entries with the correct values.

## Deployment

Run `kubectl apply -f secrets.yaml,pfadi-finder-midata-adapter.yaml,ingress.yaml,`

The `ingress.yaml` file expects traefik to be set up on the cluster, as provided by the [tractor ingress](https://github.com/scout-ch/tractor-k8s-ingress).
