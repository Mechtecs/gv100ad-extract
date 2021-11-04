{ pkgs ? import <nixpkgs> {} }:


let
    php80 = pkgs.php80.buildEnv {
        extensions = {enabled,all}: enabled ++ (with all; [
            xdebug
        ]);
    };
    in pkgs.mkShell {
      buildInputs = [
        php80
      ];
}
