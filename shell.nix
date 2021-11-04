{ pkgs ? import <nixpkgs> {} }:


let
    in pkgs.mkShell {
      buildInputs = [
        pkgs.php80
      ];
}
