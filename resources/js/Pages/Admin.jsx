import React, { useEffect, useRef, useState } from "react";

export default function Admin() {
  const audioRef = useRef(null);
  const [duration, setDuration] = useState(0);
  const [started, setStarted] = useState(false);

  useEffect(() => {
    const audio = new Audio("/sounds/concrete.mp3");
    audio.preload = "metadata";
    audioRef.current = audio;

    const onLoaded = () => {
      if (audio.duration && audio.duration > 0) setDuration(audio.duration);
    };

    audio.addEventListener("loadedmetadata", onLoaded);

    return () => {
      audio.pause();
      audio.removeEventListener("loadedmetadata", onLoaded);
    };
  }, []);

  const handleStart = async () => {
    let audio = audioRef.current;
    if (!audio) {
      audio = new Audio("/sounds/concrete.mp3");
      audio.preload = "metadata";
      audioRef.current = audio;
    }

    try {
      await audio.play();
      setStarted(true);
      if (audio.duration && audio.duration > 0) setDuration(audio.duration);
    } catch (err) {
      console.warn("Play failed on button click", err);
    }
  };

  const staticCss = `
    .admin-root {
      position: fixed;
      inset: 0;
      background-color: #000000;
      margin: 0;
      padding: 0;
      overflow: hidden;
      display: block;
    }
    .cat-image {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 100vw;
      height: 100vh;
      object-fit: cover;
      transform: translate(-150%, -50%);
      z-index: 1000;
      pointer-events: none;
    }
    .fake-button {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 999;
      color: #ffffff;
      background: #c71c1c;
      border: 2px solid #7f0f0f;
      padding: 12px 24px;
      font-size: 18px;
      font-weight: 700;
      border-radius: 8px;
      cursor: pointer;
    }
    .fake-button:hover {
      filter: brightness(1.05);
      transform: translate(-50%, -50%) scale(1.03);
    }
    @keyframes slideInCat {
      from { transform: translate(-150%, -50%); }
      to { transform: translate(-50%, -50%); }
    }
    @keyframes pulse {
      0% { transform: translate(-50%, -50%) scale(1); }
      50% { transform: translate(-50%, -50%) scale(1.02); }
      100% { transform: translate(-50%, -50%) scale(1); }
    }
    @keyframes shake {
      0% { transform: translate(-50%, -50%) rotate(0deg); }
      25% { transform: translate(-50%, -50%) rotate(-1.5deg); }
      50% { transform: translate(-50%, -50%) rotate(1.5deg); }
      75% { transform: translate(-50%, -50%) rotate(-0.8deg); }
      100% { transform: translate(-50%, -50%) rotate(0deg); }
    }
  `;

  const dynamicCss =
    started && duration > 0
      ? `
    .cat-image {
      animation: slideInCat ${duration}s linear forwards;
    }
  `
      : "";

  return (
    <div className="admin-root">
      <style>{staticCss + dynamicCss}</style>
      <img
        src="/images/cat-look.webp"
        alt="Cat"
        className="cat-image"
      />
      <button
        className="fake-button"
        onClick={(e) => {
          e.preventDefault();
          handleStart();
        }}
        aria-label="Drop database"
      >
        Drop database
      </button>
    </div>
  );
}